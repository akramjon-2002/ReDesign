<?php

namespace app\services;

use Yii;
use yii\helpers\FileHelper;

/**
 * Сервис для создания миниатюр изображений
 * Решает проблему медленной загрузки больших изображений через ngrok
 */
class ThumbnailService
{
    /**
     * Создает миниатюру для изображения
     * 
     * @param string $originalPath Относительный путь к оригинальному изображению (например, 'uploads/requests/image.jpg')
     * @param int $maxWidth Максимальная ширина миниатюры
     * @param int $maxHeight Максимальная высота миниатюры
     * @param int $quality Качество JPEG (1-100)
     * @return string|null Относительный путь к миниатюре или null при ошибке
     */
    public function createThumbnail(string $originalPath, int $maxWidth = 300, int $maxHeight = 300, int $quality = 80): ?string
    {
        $webroot = Yii::getAlias('@webroot');
        $absoluteOriginalPath = $webroot . '/' . $originalPath;

        if (!file_exists($absoluteOriginalPath)) {
            $error = "Original file not found: {$absoluteOriginalPath}";
            Yii::warning($error, __METHOD__);
            Yii::info(['error' => $error], 'THUMBNAIL_DEBUG');
            return null;
        }

        try {
            // Определяем путь для миниатюры
            $pathInfo = pathinfo($originalPath);
            $thumbnailDir = $pathInfo['dirname'] . '/thumbs';
            $thumbnailName = $pathInfo['filename'] . '_thumb.jpg';
            $thumbnailRelativePath = $thumbnailDir . '/' . $thumbnailName;
            $thumbnailAbsolutePath = $webroot . '/' . $thumbnailRelativePath;

            // Если миниатюра уже существует, возвращаем её
            if (file_exists($thumbnailAbsolutePath)) {
                Yii::info(['action' => 'thumbnail_exists', 'path' => $thumbnailRelativePath], 'THUMBNAIL_DEBUG');
                return $thumbnailRelativePath;
            }

            // Создаем директорию для миниатюр
            FileHelper::createDirectory($webroot . '/' . $thumbnailDir);

            // Получаем информацию об изображении
            $imageInfo = @getimagesize($absoluteOriginalPath);
            if ($imageInfo === false) {
                $error = "Cannot get image info: {$absoluteOriginalPath}";
                Yii::warning($error, __METHOD__);
                Yii::info(['error' => $error], 'THUMBNAIL_DEBUG');
                return null;
            }

            list($width, $height, $type) = $imageInfo;

            // Создаем исходное изображение
            $source = $this->createImageResource($absoluteOriginalPath, $type);
            if ($source === null) {
                $error = "Cannot create image resource: {$absoluteOriginalPath}, type: {$type}";
                Yii::warning($error, __METHOD__);
                Yii::info(['error' => $error, 'type' => $type], 'THUMBNAIL_DEBUG');
                return null;
            }

            // Вычисляем новые размеры с сохранением пропорций
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            
            // Если изображение меньше максимальных размеров, используем оригинальные размеры
            if ($ratio >= 1) {
                $newWidth = $width;
                $newHeight = $height;
            } else {
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
            }

            // Создаем миниатюру
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            if ($thumbnail === false) {
                imagedestroy($source);
                return null;
            }

            // Копируем с изменением размера
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Сохраняем как JPEG
            $saved = imagejpeg($thumbnail, $thumbnailAbsolutePath, $quality);

            // Освобождаем память
            imagedestroy($source);
            imagedestroy($thumbnail);

            if (!$saved) {
                Yii::warning("Failed to save thumbnail: {$thumbnailAbsolutePath}", __METHOD__);
                return null;
            }

            $originalSize = filesize($absoluteOriginalPath);
            $thumbnailSize = filesize($thumbnailAbsolutePath);
            
            Yii::info([
                'action' => 'thumbnail_created',
                'original_path' => $originalPath,
                'thumbnail_path' => $thumbnailRelativePath,
                'original_size' => $originalSize,
                'thumbnail_size' => $thumbnailSize,
                'size_reduction' => round((1 - $thumbnailSize / $originalSize) * 100, 2) . '%',
                'dimensions' => "{$newWidth}x{$newHeight}",
            ], __METHOD__);

            return $thumbnailRelativePath;

        } catch (\Throwable $e) {
            $error = "Thumbnail creation failed: {$e->getMessage()}";
            Yii::error([
                'message' => 'Thumbnail creation failed',
                'path' => $originalPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], __METHOD__);
            echo "ОШИБКА: {$error}\n"; // Для консоли
            echo "Путь: {$originalPath}\n";
            echo "Trace: {$e->getTraceAsString()}\n";
            return null;
        }
    }

    /**
     * Создает GD ресурс изображения из файла
     * 
     * @param string $path Абсолютный путь к файлу
     * @param int $type Тип изображения (IMAGETYPE_*)
     * @return resource|null
     */
    private function createImageResource(string $path, int $type)
    {
        try {
            switch ($type) {
                case IMAGETYPE_JPEG:
                    return @imagecreatefromjpeg($path);
                case IMAGETYPE_PNG:
                    return @imagecreatefrompng($path);
                case IMAGETYPE_GIF:
                    return @imagecreatefromgif($path);
                case IMAGETYPE_WEBP:
                    return @imagecreatefromwebp($path);
                default:
                    Yii::warning("Unsupported image type: {$type}", __METHOD__);
                    return null;
            }
        } catch (\Throwable $e) {
            Yii::error([
                'message' => 'Failed to create image resource',
                'path' => $path,
                'type' => $type,
                'error' => $e->getMessage(),
            ], __METHOD__);
            return null;
        }
    }
}

