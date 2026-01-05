<?php

namespace app\services;

use app\models\Texture;
use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class TextureService
{
    /**
     * Create a new texture with image upload
     *
     * @param Texture $model
     * @param UploadedFile|null $imageFile
     * @return bool
     * @throws \yii\base\Exception
     */
    public function createTexture(Texture $model, ?UploadedFile $imageFile): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($imageFile) {
                $uniqueName = uniqid('tex_') . '.' . $imageFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/textures');
                FileHelper::createDirectory($uploadPath);

                $filePath = $uploadPath . '/' . $uniqueName;
                if ($imageFile->saveAs($filePath)) {
                    $model->image_path = 'uploads/textures/' . $uniqueName;

                    // Создаем миниатюру для быстрой загрузки в интерфейсе
                    // ВАЖНО: Оригинальный файл НЕ изменяется, AI использует оригинал!
                    $thumbnailService = new ThumbnailService();
                    $thumbnailPath = $thumbnailService->createThumbnail($model->image_path, 300, 300, 80);

                    Yii::info([
                        'action' => 'texture_created',
                        'texture_path' => $model->image_path,
                        'thumbnail_path' => $thumbnailPath,
                    ], __METHOD__);
                }
            }

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Update existing texture
     *
     * @param Texture $model
     * @param UploadedFile|null $imageFile
     * @return bool
     * @throws \yii\base\Exception
     */
    public function updateTexture(Texture $model, ?UploadedFile $imageFile): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($imageFile) {
                // Delete old image and thumbnail if exists
                if ($model->image_path) {
                    $oldPath = Yii::getAlias('@webroot/') . $model->image_path;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }

                    // Удаляем старую миниатюру
                    $pathInfo = pathinfo($model->image_path);
                    $oldThumbPath = Yii::getAlias('@webroot/') . $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.jpg';
                    if (file_exists($oldThumbPath)) {
                        @unlink($oldThumbPath);
                    }
                }

                $uniqueName = uniqid('tex_') . '.' . $imageFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/textures');
                FileHelper::createDirectory($uploadPath);

                $filePath = $uploadPath . '/' . $uniqueName;
                if ($imageFile->saveAs($filePath)) {
                    $model->image_path = 'uploads/textures/' . $uniqueName;

                    // Создаем миниатюру для быстрой загрузки в интерфейсе
                    // ВАЖНО: Оригинальный файл НЕ изменяется, AI использует оригинал!
                    $thumbnailService = new ThumbnailService();
                    $thumbnailPath = $thumbnailService->createThumbnail($model->image_path, 300, 300, 80);

                    Yii::info([
                        'action' => 'texture_updated',
                        'texture_path' => $model->image_path,
                        'thumbnail_path' => $thumbnailPath,
                    ], __METHOD__);
                }
            }

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Delete texture and its image (including thumbnail)
     *
     * @param Texture $model
     * @return bool
     * @throws \Throwable
     */
    public function deleteTexture(Texture $model): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->image_path) {
                // Удаляем оригинальный файл
                $filePath = Yii::getAlias('@webroot/') . $model->image_path;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }

                // Удаляем миниатюру
                $pathInfo = pathinfo($model->image_path);
                $thumbPath = Yii::getAlias('@webroot/') . $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.jpg';
                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }
            }

            if (!$model->delete()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
