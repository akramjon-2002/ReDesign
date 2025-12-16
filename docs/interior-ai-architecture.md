# Проект визуализации интерьера — архитектура и процесс обработки

## 1) Архитектура проекта (модули, ИИ, библиотеки)

### Backend
- **Фреймворк:** Yii2 (проект основан на `yiisoft/yii2-app-basic`).
- **HTTP / загрузки:** стандартные компоненты Yii2 (`yii\web\UploadedFile`, контроллеры/экшены).
- **Очередь задач:** `yiisoft/yii2-queue` — тяжёлая обработка вынесена в Job.
- **Telegram интеграция:** `irazasyed/telegram-bot-sdk` + собственные сервисы/контроллеры в `modules/telegram`.
- **Логи:** `Yii::info / Yii::warning / Yii::error` пишутся в `runtime/logs/*`.

### ИИ / генерация изображений
- Используется **Stability AI API v2beta** через собственный сервис:
  - `services/StabilityService.php`
- В коде реализованы вызовы эндпоинтов:
  - `stable-image/edit/inpaint` (инпейнт по маске)
  - `stable-image/edit/search-and-replace` (поиск области по тексту + замена)
  - также есть генерация (`generate/ultra`, `generate/sd3`) и `image-to-image` через SD3 эндпоинт

Важно: **прямого ControlNet/Depth/Segmentation в коде нет** — управление областью достигается либо маской (inpaint), либо текстовым поиском объекта/области (search-and-replace).


## 2) Как работает процесс: загрузка фото → обработка → результат

### Шаг A — загрузка изображения (WebApp)
- **Код:** `modules/telegram/actions/UploadAction.php::run()`
- Принимает:
  - `user_id` (чат/пользователь)
  - `texture_id` (выбранная текстура)
  - файл `photo` (интерьер)
- Делает:
  - логирует входные данные
  - достаёт текстуру из БД (`Texture::findOne($textureId)`)
  - формирует усиленный `prompt` для ИИ:
    - менять **только поверхности стен** (`ONLY the wall surfaces`)
    - сохранить **двери/окна/мебель/пол/потолок/молдинги/декор** как в оригинале
    - для красных текстур автоматически добавляется уточнение цвета:
      - `deep burgundy red color, wine red, maroon, RGB(90,0,0), hex #5A0000`
    - если в текстуре обнаружен паттерн (обои/плитка), добавляется фраза:
      - `textured wall surface with ...`
  - ставит задачу в очередь через `RequestService`

### Шаг B — сохранение входного фото и создание Request
- **Код:** `services/RequestService.php::createAndEnqueueStability()`
- Делает:
  - сохраняет загруженный файл в `web/uploads/requests/in_*.jpg`
  - создаёт запись `Request` в БД (статус `new`, пути входа/выхода)
  - пушит `jobs/StabilityJob` в очередь (`Yii::$app->queue->push(...)`)

### Шаг C — выполнение Job (генерация результата)
- **Код:** `jobs/StabilityJob.php::execute()`
- Делает:
  - переводит `Request` в статус `processing`
  - выбирает режим обработки по `mode`
  - вызывает `StabilityService` (inpaint/search-and-replace)
  - сохраняет результат в `web/uploads/requests/out_<id>_*.png`
  - ставит статус `completed` и отправляет результат в Telegram

Также на уровне Job:
- используется расширенный `negative_prompt`, запрещающий "новые двери/окна", изменение структуры комнаты и добавление мебели/декора.


## 3) Используется ли маска/сегментация для выделения стен?

### Основной метод: HuggingFace SegFormer (нейросегментация)
- **Код:** `services/HuggingFaceService.php`
- **Модель:** `nvidia/segformer-b0-finetuned-ade-512-512` (ADE20K dataset, 150 классов)
- Что делает:
  - отправляет изображение на HuggingFace Inference API
  - получает сегментацию с классами: wall, door, window, floor, ceiling, furniture и др.
  - извлекает маску для класса `wall`
  - отсекает потолок (верхние 15%) и пол (нижние 15%) из маски
  - применяет Gaussian blur для мягких границ
- **Стоимость:** ~$0.001-0.003 за запрос (Free tier: $0.10/месяц)
- **Настройка:** ключ `huggingface_api_key` в `config/params.php`

### Fallback: Flood Fill (если HuggingFace недоступен)
- **Код:** `jobs/StabilityJob.php::buildAutoWallMaskWithRatio()`
- Что делает:
  - грузит изображение через GD
  - уменьшает до превью ~320px
  - запускает **flood fill** от набора seed-точек по сетке (9–12 точек):
    - левый сектор (левая стена): 30%/50%/70% по высоте
    - центр (задняя/центральная стена): 15%/20%/25%/35%/50%/65%/80% по высоте
    - правый сектор (правая стена): 30%/50%/70% по высоте
  - находит тёмные зоны (часто двери/окна) и исключает их из заливки:
    - `detectDarkZones()` по яркости < 50 и контрасту > 80
    - детектирует двери по аспекту (1:2 - 1:4) и высоте (> 30%)
    - `isInExcludedZone()` проверяет попадание точки/пикселя в исключённую зону
  - строит бинарную маску (белое = менять)
  - пишет статистику `filled_ratio`

После построения маска дополнительно улучшается:
- `refineMask()`:
  - морфология: closing (закрыть дырки) + opening (убрать мелкие фрагменты)
  - отсекает потолок (верхние 25%) и пол (нижние 20%) в маске
  - исключает яркие пиксели (lum > 200) в верхней половине (белый потолок)
  - размывает границы маски (Gaussian blur 2 раза) для мягкого перехода

### Логика выбора метода
- **Код:** `jobs/StabilityJob.php::runAutoWallInpaint()`
- Порядок:
  1. Пробуем HuggingFace SegFormer (если API ключ настроен)
  2. Если HF не сработал — fallback на flood fill
  3. Если `filled_ratio >= 0.60` — fallback на `search-and-replace`
  4. Если маска фрагментирована (`regions > 2`, только для flood fill) — fallback на `search-and-replace`


## 4) Какая модель генерации изображений (Stable Diffusion, DALL-E, другая)?

В проекте используется **Stability AI**.
- Для генерации "с нуля" в сервисе есть:
  - `generateUltra()` → `.../stable-image/generate/ultra`
  - `generateSD3()` → `.../stable-image/generate/sd3`
- Для редактирования интерьера используются:
  - `inpaint()` → `.../stable-image/edit/inpaint`
  - `searchAndReplace()` → `.../stable-image/edit/search-and-replace`

Точная внутренняя модель для `edit/*` у Stability может быть абстрагирована API, но по коду видно, что в проекте ставка на экосистему **Stable Diffusion / SD3 (Stability)**, а не DALL-E.


## 5) Есть ли контроль областей изменения (inpainting, controlnet)?

### Inpainting — да
- **Код:** `services/StabilityService.php::inpaint()`
- Управление областью:
  - параметр `mask` (белое = область редактирования)

Дополнительно в текущей реализации:
- используется `style_preset=photographic` (реалистичность)
- `strength=0.85` (меньше ломает детали)
- `seed` задаётся (если не передан — случайный)
- есть retry (до 2 попыток) при эвристически "плохом" результате

### ControlNet — нет
- В коде нет передачи control image / depth / canny / segmentation / pose.
- Никаких controlnet-параметров в запросе не формируется.

### Search-and-replace — "контроль" через текст
- **Код:** `services/StabilityService.php::searchAndReplace()`
- Управление областью:
  - `search_prompt` — что искать
  - `prompt` — на что заменить

В текущей реализации `search_prompt` усилен и конкретизирован:
- `all interior wall surfaces, left wall, right wall, back wall, painted walls, wall area excluding doors and windows`

А `prompt` для режима search-and-replace дополняется контекстом:
- "Change the color of all visible walls to ..."
- "Preserve the exact position and appearance of doors/windows/outlets/switches/moldings/baseboards..."
- "Do not add new architectural elements, do not change room layout, do not modify non-wall surfaces"

Это слабее, чем маска/ControlNet: область ищется моделью по тексту и может быть распознана неточно.


## 6) Как обрабатываются элементы типа дверей, окон, мебели?

### Текущая стратегия проекта
- НЕТ отдельного детектора дверей/окон/мебели.
- Используются:
  - ограничения в `prompt` ("не добавляй/не убирай мебель")
  - `negative_prompt` (запрещаем постеры/мебель/декор)
  - маска стен (когда удаётся)

### Где это задаётся
- `modules/telegram/actions/UploadAction.php::run()` — формирует `prompt`
- `jobs/StabilityJob.php::$negativePrompt` — общий запрет нежелательных объектов

В `negative_prompt` дополнительно запрещаются:
- `new doors`, `new windows`
- `changed layout`, `different room structure`
- `modified ceiling`, `changed floor`
- `added furniture`, `extra decorations`

Это работает частично: модель всё равно может менять мелкие детали/границы, если маска неточная или область замены большая.


## 7) Почему модель добавляет лишние элементы и ошибается с границами стен?

### Причина A — генеративная природа модели
Даже в режиме редактирования модель "достраивает" вероятные детали.
Если область редактирования большая или неясная, она может дорисовать:
- двери, проёмы
- плинтуса, молдинги
- декор
- мебель

### Причина B — большая область замены (маска/поиск)
- При **большой маске** inpaint часто начинает воспринимать задачу как генерацию сцены.
- Поэтому добавлен guard `filled_ratio >= 0.50` → `search-and-replace`, и дополнительный guard по фрагментации маски (`regions > 2`).

### Причина C — неточной маски стен
Авто-маска сейчас — flood fill по цвету:
- стены могут иметь градиенты/тени
- стена и потолок/пол могут быть близки по цвету
- окна/двери/проёмы нарушают однородность

В итоге маска может:
- недокрыть стену
- "залезть" на потолок/пол
- обойти края, потому что цвет отличается из-за теней

### Причина D — search-and-replace не гарантирует "все стены"
`search_prompt` — текстовая подсказка. Если модель распознала только одну стену, вторую пропустит.
Мы усилили `search_prompt` до: `wall, walls, all wall surfaces, interior walls`, но это всё равно эвристика.


# Ключевые участки кода (по твоему списку)

## Загрузка и препроцессинг изображения
- `modules/telegram/actions/UploadAction.php`
  - `run()` — принимает файл, выбирает texture, строит prompt
  - `buildAutoTextureDescription()` — анализ изображения текстуры через GD
  - `approxPattern()` — грубая эвристика паттерна (обои/плитка) по edge/variance
  - `gdLoadImage()` — загрузка png/jpg/webp/avif

## Генерация/изменение текстур
- `services/StabilityService.php`
  - `inpaint()` — редактирование по маске (white area)
  - `searchAndReplace()` — редактирование по текстовому поиску области
  - `sendRequest()` — HTTP multipart (cURL) к Stability AI

## Маскирование областей
- `jobs/StabilityJob.php`
  - `runAutoWallInpaint()` — выбор: inpaint по маске или fallback на search-and-replace
  - `buildAutoWallMaskWithRatio()` — генерация маски стен (flood fill)
  - `floodFillAppend()` / `countMaskFilled()` — вспомогательные функции
  - `countMaskRegions()` — подсчёт числа отдельных областей маски (connected components)


# Практические ограничения текущей реализации
- **Нет настоящей сегментации стен** (нужен отдельный ML-сегментатор).
- **Нет ControlNet**, поэтому точность границ ограничена.
- **Search-and-replace** не гарантирует покрытие всех стен.


# Что улучшать дальше (варианты)

## Вариант 1 (лучший по качеству): настоящая сегментация стен
- Подключить сегментацию стен (SAM/Mask2Former/DeepLab) внешним сервисом.
- Сохранять точную маску и всегда использовать `inpaint`.

## Вариант 2 (без ML): улучшить авто-маску
- Увеличить число seed-точек и добавить отдельные seed для левой/правой стен.
- Добавить адаптивный порог `maxDist` по локальной дисперсии.
- Отсечь окна/проёмы через простые правила (например, исключить очень тёмные/очень контрастные зоны).

## Вариант 3: жёстче задавать цвет
- В `prompt` добавлять явный оттенок + hex/описание:
  - "deep dark red / burgundy / maroon" + "hex #5A0000" (модель не всегда соблюдает hex, но помогает)

Сейчас в проекте для красных текстур автоматически добавляется уточнение:
- `deep burgundy red color, wine red, maroon, RGB(90,0,0), hex #5A0000`

