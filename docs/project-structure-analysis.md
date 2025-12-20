# Анализ структуры проекта Interior AI Design

## 1. СТРУКТУРА ПРОЕКТА

### Основные папки и файлы:
```
design/
├── assets/           # Asset bundles для frontend
├── commands/         # Console команды Yii2
├── config/          # Конфигурации (web.php, db.php, console.php)
├── controllers/     # Основные контроллеры (SiteController)
├── docs/           # Документация
├── jobs/           # Background job'ы (GeminiJob)
├── mail/           # Email шаблоны
├── messages/       # Переводы (uz/app.php)
├── migrations/     # Миграции БД
├── models/         # Модели (User, Texture, Color, Request)
├── modules/        # Модули приложения
│   ├── admin/      # Админ панель
│   └── telegram/   # Telegram WebApp
├── runtime/        # Кеш, логи, временные файлы
├── services/       # Сервисы (GeminiService, TelegramService)
├── tests/          # Тесты
├── vendor/         # Composer dependencies
├── views/          # Основные views
├── web/            # Web root
│   ├── assets/     # Compiled assets
│   ├── css/        # CSS файлы (site.css)
│   ├── uploads/    # Загруженные файлы
│   │   ├── textures/  # Изображения текстур
│   │   └── requests/  # Загруженные пользователями изображения
│   └── index.php   # Entry point
└── widgets/        # Custom виджеты
```

### Views, Controllers, Models:
- **Controllers**: `/controllers/SiteController.php`, `/modules/admin/controllers/`, `/modules/telegram/controllers/`
- **Models**: `/models/` (User, Texture, Color, Request, ContactForm, LoginForm)
- **Views**: `/views/`, `/modules/admin/views/`, `/modules/telegram/views/`

### JavaScript/CSS файлы:
- **CSS**: `/web/css/site.css`
- **JavaScript**: Встроен в `/modules/telegram/views/webapp/index.php` (строки 222-401)
- **Assets**: `/assets/AppAsset.php` управляет подключением ресурсов

## 2. TELEGRAM WEBAPP

### Файлы связанные с Telegram WebApp:

1. **Модуль**: `/modules/telegram/Module.php`
2. **Контроллеры**:
   - `/modules/telegram/controllers/WebappController.php`
   - `/modules/telegram/controllers/WebhookController.php`
3. **Actions**: `/modules/telegram/actions/UploadAction.php`
4. **Views**: `/modules/telegram/views/webapp/index.php`
5. **Services**: `/services/TelegramService.php`, `/services/TelegramUpdateService.php`

### Код показа текстур пользователю:

**Файл**: `/modules/telegram/views/webapp/index.php` (строки 22-31)
```php
<select id="textureId" name="texture_id" class="form-select">
    <option value="">-- <?= Yii::t('app', 'no texture') ?> --</option>
    <?php foreach ($textures as $texture): ?>
        <option value="<?= (int)$texture->id ?>" data-preview="<?= Html::encode($texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : '') ?>">
            <?= Html::encode($texture->title) ?>
        </option>
    <?php endforeach; ?>
</select>
```

### Формирование URL для изображений:

**Строка 24**: 
```php
data-preview="<?= Html::encode($texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : '') ?>"
```

**JavaScript обработка превью** (строки 277-294):
```javascript
textureSelect.addEventListener('change', function() {
  var opt = textureSelect.options[textureSelect.selectedIndex];
  var url = opt ? opt.getAttribute('data-preview') : '';
  if (url && previewWrap && previewImg) {
    previewImg.onerror = function() {
      previewWrap.style.display = 'none';
    };
    previewImg.onload = function() {
      previewWrap.style.display = 'block';
    };
    previewImg.src = url;
  }
});
```

## 3. ПРОБЛЕМА: GET /uploads/image/ HTTP/1.1" 404

### Анализ ошибки:
- **User-Agent**: "Dart/3.9 (dart:io)" - запрос от Flutter приложения
- **URL**: GET `/uploads/image/` - запрашивается папка, не файл
- **Физически есть**: `/web/uploads/textures/` и `/web/uploads/requests/`
- **НЕТ папки**: `/web/uploads/image/`

## 4. ИСТОЧНИКИ ПРОБЛЕМЫ

### Где упоминается "uploads/image":
Поиск показал: **НЕТ прямых упоминаний "uploads/image" в коде!**

### Реальные пути к изображениям из БД:

**Таблица `textures`**:
```sql
SELECT id, title, image_path FROM textures;
```

**Результат**:
- ID 4: `uploads/textures/tex_6944fa50370cc.webp`
- ID 3: `uploads/textures/tex_6944fa3b90c8c.jpg`  
- ID 2: `uploads/textures/tex_6944fa26ccd94.jpg`
- ID 1: `uploads/textures/tex_694149a0e5295.jpg`

**Таблица `requests`**:
- `input_image_path`: `uploads/requests/in_*.jpg`
- `output_image_path`: `uploads/requests/out_*.png`

### Правильные пути ДОЛЖНЫ быть:
- **Текстуры**: `/uploads/textures/tex_*.{jpg|webp|png}`
- **Входные изображения**: `/uploads/requests/in_*.jpg`
- **Выходные изображения**: `/uploads/requests/out_*.png`

## 5. ПРИЧИНА ПРОБЛЕМЫ

### Возможные источники `/uploads/image/`:

1. **Flutter клиент** неправильно парсит URL из `data-preview`
2. **Кеширование** в WebView/Flutter
3. **Старые данные** в кеше Flutter приложения
4. **JavaScript ошибка** при обработке URL
5. **Proxy/CDN** модификация URL

### Формирование URL в коде:

**Исходный код** (`/modules/telegram/views/webapp/index.php:24`):
```php
$texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : ''
```

**Что получается** (при `baseUrl = ''`):
- БД: `uploads/textures/tex_6944fa50370cc.webp`
- URL: `/uploads/textures/tex_6944fa50370cc.webp` ✅

**Что запрашивает Flutter**:
- Запрос: `/uploads/image/` ❌




