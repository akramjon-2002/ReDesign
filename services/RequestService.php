<?php

namespace app\services;

use app\jobs\GeminiJob;
use app\models\Request;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class RequestService
{
    /**
     * Create request and enqueue Gemini job for image editing
     *
     * @param int|string $userId User/Chat ID
     * @param int|null $textureId Texture ID (optional)
     * @param UploadedFile $imageFile Uploaded image file
     * @param string|null $color HEX color (optional)
     * @param string|null $aspectRatio Aspect ratio (optional)
     * @return Request
     */
    public function createAndEnqueueGemini($userId, ?int $textureId, UploadedFile $imageFile, ?string $color = null, ?string $aspectRatio = null): Request
    {
        Yii::info([
            'action' => 'create_gemini_request',
            'user_id' => $userId,
            'texture_id' => $textureId,
            'color' => $color,
            'aspect_ratio' => $aspectRatio,
        ], __METHOD__);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $uploadDir = Yii::getAlias('@webroot/uploads/requests');
            FileHelper::createDirectory($uploadDir);

            $fileName = uniqid('in_', true) . '.' . $imageFile->extension;
            $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
            if (!$imageFile->saveAs($absolutePath)) {
                throw new \RuntimeException('Failed to save input image.');
            }

            // Создаем миниатюру для быстрой загрузки в галерее
            $relativePath = 'uploads/requests/' . $fileName;
            $thumbnailService = new ThumbnailService();
            $thumbnailPath = $thumbnailService->createThumbnail($relativePath, 300, 300, 80);

            Yii::info([
                'step' => 'request_image_saved',
                'absolute_path' => $absolutePath,
                'relative_path' => $relativePath,
                'thumbnail_path' => $thumbnailPath,
                'file_size' => is_file($absolutePath) ? filesize($absolutePath) : null,
            ], __METHOD__);

            $request = new Request();
            $request->user_id = $userId;
            $request->texture_id = $textureId;
            $request->color_hex = $color;
            $request->aspect_ratio = $aspectRatio;
            $request->input_image_path = 'uploads/requests/' . $fileName;
            $request->status = Request::STATUS_NEW;

            if (!$request->save()) {
                throw new \RuntimeException('Failed to create request: ' . json_encode($request->getFirstErrors()));
            }

            Yii::info([
                'step' => 'request_created',
                'request_id' => $request->id,
                'status' => $request->status,
            ], __METHOD__);

            Yii::$app->queue->push(new GeminiJob([
                'requestId' => $request->id,
                'textureId' => $textureId,
                'color' => $color,
                'aspectRatio' => $aspectRatio,
            ]));

            Yii::info([
                'step' => 'job_enqueued',
                'request_id' => $request->id,
                'texture_id' => $textureId,
                'color' => $color,
                'aspect_ratio' => $aspectRatio,
            ], __METHOD__);

            $transaction->commit();
            return $request;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
