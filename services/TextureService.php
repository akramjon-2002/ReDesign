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
                // Delete old image if exists
                if ($model->image_path) {
                    $oldPath = Yii::getAlias('@webroot/') . $model->image_path;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $uniqueName = uniqid('tex_') . '.' . $imageFile->extension;
                $uploadPath = Yii::getAlias('@webroot/uploads/textures');
                FileHelper::createDirectory($uploadPath);
                
                $filePath = $uploadPath . '/' . $uniqueName;
                if ($imageFile->saveAs($filePath)) {
                    $model->image_path = 'uploads/textures/' . $uniqueName;
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
     * Delete texture and its image
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
                $filePath = Yii::getAlias('@webroot/') . $model->image_path;
                if (file_exists($filePath)) {
                    @unlink($filePath);
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
