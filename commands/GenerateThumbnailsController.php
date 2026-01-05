<?php

namespace app\commands;

use app\models\Texture;
use app\services\ThumbnailService;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Генерация миниатюр для существующих изображений
 * 
 * Использование:
 * php yii generate-thumbnails/index
 */
class GenerateThumbnailsController extends Controller
{
    /**
     * Генерирует миниатюры для всех изображений в uploads/requests
     */
    public function actionIndex()
    {
        $this->stdout("Начинаем генерацию миниатюр...\n", Console::FG_GREEN);
        
        $webroot = Yii::getAlias('@webroot');
        $uploadsDir = $webroot . '/uploads/requests';
        
        if (!is_dir($uploadsDir)) {
            $this->stdout("Директория {$uploadsDir} не найдена\n", Console::FG_RED);
            return 1;
        }
        
        $thumbnailService = new ThumbnailService();
        $files = glob($uploadsDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        
        if (empty($files)) {
            $this->stdout("Изображения не найдены\n", Console::FG_YELLOW);
            return 0;
        }
        
        $total = count($files);
        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = 0;
        
        $this->stdout("Найдено изображений: {$total}\n\n", Console::FG_CYAN);
        
        foreach ($files as $file) {
            $processed++;
            $filename = basename($file);
            $relativePath = 'uploads/requests/' . $filename;
            
            $this->stdout("[{$processed}/{$total}] Обработка: {$filename}... ", Console::FG_CYAN);
            
            // Проверяем, существует ли уже миниатюра
            $thumbPath = str_replace($filename, 'thumbs/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg', $file);
            if (file_exists($thumbPath)) {
                $this->stdout("пропущено (уже существует)\n", Console::FG_YELLOW);
                $skipped++;
                continue;
            }
            
            try {
                $result = $thumbnailService->createThumbnail($relativePath, 300, 300, 80);
                
                if ($result) {
                    $originalSize = filesize($file);
                    $thumbSize = filesize($webroot . '/' . $result);
                    $reduction = round((1 - $thumbSize / $originalSize) * 100, 1);
                    
                    $this->stdout("создано ", Console::FG_GREEN);
                    $this->stdout("(размер: " . $this->formatBytes($originalSize) . " → " . $this->formatBytes($thumbSize) . ", -{$reduction}%)\n");
                    $created++;
                } else {
                    $this->stdout("ошибка\n", Console::FG_RED);
                    $errors++;
                }
            } catch (\Throwable $e) {
                $this->stdout("ошибка: {$e->getMessage()}\n", Console::FG_RED);
                $errors++;
            }
        }
        
        $this->stdout("\n" . str_repeat("=", 60) . "\n", Console::FG_CYAN);
        $this->stdout("Обработано: {$processed}\n", Console::FG_CYAN);
        $this->stdout("Создано: {$created}\n", Console::FG_GREEN);
        $this->stdout("Пропущено: {$skipped}\n", Console::FG_YELLOW);
        $this->stdout("Ошибок: {$errors}\n", $errors > 0 ? Console::FG_RED : Console::FG_CYAN);
        $this->stdout(str_repeat("=", 60) . "\n\n", Console::FG_CYAN);
        
        return 0;
    }
    
    /**
     * Генерирует миниатюры для всех текстур из базы данных
     * ВАЖНО: Оригинальные файлы текстур НЕ изменяются!
     * Миниатюры создаются только для показа в интерфейсе.
     * AI продолжает использовать оригинальные текстуры.
     */
    public function actionTextures()
    {
        $this->stdout("Генерация миниатюр для текстур из базы данных...\n", Console::FG_GREEN);
        $this->stdout("ВАЖНО: Оригинальные файлы НЕ изменяются!\n\n", Console::FG_YELLOW);

        $textures = Texture::find()->all();

        if (empty($textures)) {
            $this->stdout("Текстуры не найдены в базе данных\n", Console::FG_YELLOW);
            return 0;
        }

        $total = count($textures);
        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = 0;

        $this->stdout("Найдено текстур в БД: {$total}\n\n", Console::FG_CYAN);

        $thumbnailService = new ThumbnailService();
        $webroot = Yii::getAlias('@webroot');

        foreach ($textures as $texture) {
            $processed++;

            if (empty($texture->image_path)) {
                $this->stdout("[{$processed}/{$total}] ID {$texture->id}: пропущено (нет image_path)\n", Console::FG_YELLOW);
                $skipped++;
                continue;
            }

            $this->stdout("[{$processed}/{$total}] ID {$texture->id} ({$texture->title}): ", Console::FG_CYAN);

            $originalFile = $webroot . '/' . $texture->image_path;
            if (!file_exists($originalFile)) {
                $this->stdout("файл не найден ({$texture->image_path})\n", Console::FG_RED);
                $errors++;
                continue;
            }

            // Проверяем, существует ли уже миниатюра
            $pathInfo = pathinfo($texture->image_path);
            $thumbPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.jpg';
            $thumbFile = $webroot . '/' . $thumbPath;

            if (file_exists($thumbFile)) {
                $this->stdout("пропущено (миниатюра уже существует)\n", Console::FG_YELLOW);
                $skipped++;
                continue;
            }

            try {
                $result = $thumbnailService->createThumbnail($texture->image_path, 300, 300, 80);

                if ($result) {
                    $originalSize = filesize($originalFile);
                    $thumbSize = filesize($webroot . '/' . $result);
                    $reduction = round((1 - $thumbSize / $originalSize) * 100, 1);

                    $this->stdout("создано ", Console::FG_GREEN);
                    $this->stdout("(оригинал: " . $this->formatBytes($originalSize) . " → миниатюра: " . $this->formatBytes($thumbSize) . ", -{$reduction}%)\n");
                    $created++;
                } else {
                    // Проверяем логи для детальной информации
                    $this->stdout("ошибка создания", Console::FG_RED);
                    $this->stdout(" (проверьте runtime/logs/app.log для деталей)\n", Console::FG_YELLOW);
                    $this->stdout("    Путь: {$texture->image_path}\n", Console::FG_YELLOW);
                    $errors++;
                }
            } catch (\Throwable $e) {
                $this->stdout("ошибка: {$e->getMessage()}\n", Console::FG_RED);
                $this->stdout("    Путь: {$texture->image_path}\n", Console::FG_YELLOW);
                $errors++;
            }
        }

        $this->stdout("\n" . str_repeat("=", 70) . "\n", Console::FG_CYAN);
        $this->stdout("РЕЗУЛЬТАТЫ ГЕНЕРАЦИИ МИНИАТЮР ДЛЯ ТЕКСТУР:\n", Console::FG_CYAN);
        $this->stdout(str_repeat("=", 70) . "\n", Console::FG_CYAN);
        $this->stdout("Обработано текстур: {$processed}\n", Console::FG_CYAN);
        $this->stdout("Создано миниатюр: {$created}\n", Console::FG_GREEN);
        $this->stdout("Пропущено: {$skipped}\n", Console::FG_YELLOW);
        $this->stdout("Ошибок: {$errors}\n", $errors > 0 ? Console::FG_RED : Console::FG_CYAN);
        $this->stdout(str_repeat("=", 70) . "\n", Console::FG_CYAN);
        $this->stdout("\nВАЖНО: Оригинальные файлы текстур сохранены без изменений!\n", Console::FG_GREEN);
        $this->stdout("AI продолжает использовать оригинальные текстуры для генерации.\n", Console::FG_GREEN);
        $this->stdout("Миниатюры используются только для показа в интерфейсе.\n\n", Console::FG_GREEN);

        return 0;
    }

    /**
     * Форматирует размер в байтах в читаемый формат
     *
     * @param int $bytes Размер в байтах
     * @return string Форматированная строка
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}

