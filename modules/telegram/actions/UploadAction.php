<?php

namespace app\modules\telegram\actions;

use app\models\Texture;
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

            $imageFile = UploadedFile::getInstanceByName('photo');
            if ($imageFile === null) {
                throw new BadRequestHttpException('photo file is required');
            }

            $texturePrompt = '';
            if ($textureId !== null) {
                $texture = Texture::findOne($textureId);
                if ($texture !== null && !empty($texture->prompt_suffix)) {
                    $texturePrompt = $texture->prompt_suffix;
                }
            }

            if ($texturePrompt === '') {
                $texturePrompt = 'wallpaper texture';
            }

            $prompt = "{$texturePrompt}, photorealistic, high quality, same lighting";

            Yii::info([
                'action' => 'upload_for_stability',
                'user_id' => $userId,
                'texture_id' => $textureId,
                'prompt' => $prompt,
            ], __METHOD__);

            $request = $this->requestService->createAndEnqueueStability($userId, $textureId, $imageFile, $prompt, 'wall');

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
