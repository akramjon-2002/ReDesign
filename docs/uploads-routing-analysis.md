# Анализ проекта Interior AI Design - Решение проблемы uploads/image/

## 1. СТРУКТУРА ПРОЕКТА

### Основные компоненты:
- **Framework**: Yii2 Basic Application Template
- **Database**: PostgreSQL (пользователь: postgres, БД: design)
- **Язык**: Узбекский (uz) с исходным английским
- **Модули**: Admin, Telegram

### Контроллеры:
- `SiteController` - основной контроллер
- `admin/TextureController` - управление текстурами
- `admin/ColorController` - управление цветами  
- `telegram/WebappController` - Telegram WebApp
- `telegram/WebhookController` - Telegram webhook

### Модели:
- `Texture` - текстуры с изображениями
- `Request` - запросы на обработку изображений
- `Color` - цвета
- `User`, `Role` - пользователи и роли

## 2. КОНФИГУРАЦИЯ

### URL Manager:
```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'core' => 'admin/texture/gallery',
        'core/<controller:\w+>' => 'admin/<controller>/index',
        'core/<controller:\w+>/<action:\w+>' => 'admin/<controller>/<action>',
        'telegram/webhook' => 'telegram/webhook/index',
        'telegram/webapp' => 'telegram/webapp/index',
        'telegram/webapp/upload' => 'telegram/webapp/upload',
    ],
],
```

### .htaccess:
```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```

### AssetManager:
- Базовый путь: `@webroot`
- Базовый URL: `@web`

## 3. ТЕКУЩАЯ ПРОБЛЕМА

**Ошибка**: `yii\base\InvalidRouteException: Unable to resolve the request "uploads/image/"`

### Корень проблемы:
1. **Файлы uploads находятся в**: `/web/uploads/`
   - `/web/uploads/textures/` - изображения текстур
   - `/web/uploads/requests/` - загруженные пользователями изображения

2. **Правила .htaccess** перенаправляют все несуществующие файлы и папки в `index.php`

3. **URL Manager** не имеет правила для обработки `uploads/image/`

4. **Yii2 пытается найти контроллер** `UploadsController` с действием `actionImage()`

## 4. АНАЛИЗ ЗАГРУЗКИ ФАЙЛОВ

### TextureService:
- Сохраняет в: `@webroot/uploads/textures/`
- Путь в БД: `uploads/textures/filename.ext`
- Уникальное имя: `tex_` + uniqid

### RequestService:
- Сохраняет в: `@webroot/uploads/requests/`
- Путь в БД: `uploads/requests/filename.ext`  
- Уникальное имя: `in_` + uniqid

### Физическое расположение:
```
/web/uploads/
├── textures/
│   ├── tex_693fe1176231c.jpg
│   ├── tex_693fec76b9fbe.jpg
│   └── ...
└── requests/
    └── (загруженные пользователями файлы)
```

## 5. РЕШЕНИЕ

### Проблема: 
**URL `uploads/image/` попадает в Yii2 вместо прямой раздачи статических файлов**

### Варианты решения:

#### Вариант 1: Обновить .htaccess (РЕКОМЕНДУЕТСЯ)
Добавить исключение для папки uploads:

```apache
RewriteEngine on
# Исключить папку uploads из перенаправления
RewriteRule ^uploads/ - [L]
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```

#### Вариант 2: Создать контроллер UploadsController
Если нужна обработка запросов через Yii2:

```php
<?php
namespace app\controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UploadsController extends Controller
{
    public function actionImage($path = null)
    {
        // Логика обработки запросов к изображениям
        throw new NotFoundHttpException('Image not found');
    }
}
```

#### Вариант 3: Добавить правило в urlManager
```php
'rules' => [
    'uploads/<path:.*>' => 'uploads/file',
    // ... остальные правила
],
```

### Рекомендация:
**Использовать Вариант 1** - обновить .htaccess, так как:
- Статические файлы должны раздаваться напрямую веб-сервером
- Это более эффективно по производительности
- Не нагружает PHP/Yii2 приложение

## 6. ДОПОЛНИТЕЛЬНЫЕ РЕКОМЕНДАЦИИ

1. **Добавить robots.txt исключения** для папки uploads
2. **Настроить кеширование** для статических изображений
3. **Добавить проверки безопасности** для доступа к файлам
4. **Рассмотреть использование CDN** для изображений

## 7. ТЕСТИРОВАНИЕ

После применения решения проверить:
1. Прямой доступ к файлам: `/uploads/textures/tex_693fe1176231c.jpg`
2. Работу приложения: все маршруты должны работать корректно
3. Загрузку новых файлов через UploadAction

---

**Дата анализа**: 20 декабря 2025  
**Статус**: Готово к внедрению