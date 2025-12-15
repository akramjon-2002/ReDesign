<?php

namespace app\services;

use app\jobs\StabilityJob;
use app\models\Request;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class RequestService
{
    /**
     * Create request and enqueue Stability AI job for image editing
     *
     * @param int|string $userId User/Chat ID
     * @param int|null $textureId Texture ID (optional)
     * @param UploadedFile $imageFile Uploaded image file
     * @param string $prompt Text prompt for Stability AI
     * @param string $searchPrompt What to search/replace (default: wall)
     * @return Request
     */
    public function createAndEnqueueStability($userId, ?int $textureId, UploadedFile $imageFile, string $prompt, string $searchPrompt = 'wall'): Request
    {
        Yii::info([
            'action' => 'create_stability_request',
            'user_id' => $userId,
            'texture_id' => $textureId,
            'prompt' => mb_substr($prompt, 0, 100),
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

            $request = new Request();
            $request->user_id = $userId;
            $request->texture_id = $textureId;
            $request->input_image_path = 'uploads/requests/' . $fileName;
            $request->status = Request::STATUS_NEW;

            if (!$request->save()) {
                throw new \RuntimeException('Failed to create request: ' . json_encode($request->getFirstErrors()));
            }

            Yii::$app->queue->push(new StabilityJob([
                'requestId' => $request->id,
                'prompt' => $prompt,
                'searchPrompt' => $searchPrompt,
                'mode' => 'search-and-replace',
            ]));

            $transaction->commit();
            return $request;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
