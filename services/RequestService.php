<?php

namespace app\services;

use app\jobs\ReplicateJob;
use app\models\Request;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class RequestService
{
    public function createAndEnqueue($userId, ?int $textureId, UploadedFile $imageFile, array $replicateInput = [], ?string $versionId = null): Request
    {
        if ($versionId === null) {
            $versionId = Yii::$app->params['replicate_model_version'] ?? null;
        }
        if (empty($versionId)) {
            throw new InvalidArgumentException('Replicate model version is not configured. Set params[replicate_model_version] or pass $versionId.');
        }

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

            Yii::$app->queue->push(new ReplicateJob([
                'requestId' => $request->id,
                'versionId' => $versionId,
                'input' => $replicateInput,
            ]));

            $transaction->commit();
            return $request;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
