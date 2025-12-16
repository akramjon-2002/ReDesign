<?php

namespace app\modules\telegram\actions;

use app\services\RequestService;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    private RequestService $requestService;

    public function __construct($id, $controller, RequestService $requestService, $config = [])
    {
        $this->requestService = $requestService;
        parent::__construct($id, $controller, $config);
    }

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $userId = Yii::$app->request->post('user_id');
            if ($userId === null || $userId === '') {
                throw new BadRequestHttpException('user_id is required');
            }

            $textureId = Yii::$app->request->post('texture_id');
            $textureId = ($textureId === null || $textureId === '') ? null : (int)$textureId;

            $color = Yii::$app->request->post('color');
            $color = (is_string($color) && $color !== '') ? $color : null;

            $imageFile = UploadedFile::getInstanceByName('photo');
            if ($imageFile === null) {
                throw new BadRequestHttpException('photo file is required');
            }

            if ($textureId === null && $color === null) {
                throw new BadRequestHttpException('Please select a texture or color');
            }

            Yii::info([
                'step' => 'upload_action_received',
                'user_id' => $userId,
                'texture_id' => $textureId,
                'color' => $color,
                'photo_name' => $imageFile->name,
                'photo_size' => $imageFile->size,
            ], __METHOD__);

            $request = $this->requestService->createAndEnqueueGemini(
                $userId,
                $textureId,
                $imageFile,
                $color
            );

            return [
                'ok' => true,
                'request_id' => $request->id,
                'status' => $request->status,
            ];
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
