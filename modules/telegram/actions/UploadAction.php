<?php

namespace app\modules\telegram\actions;

use app\models\Texture;
use app\services\RequestService;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
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

        $replicateInput = [];

        $mime = $imageFile->type ?: 'image/jpeg';
        $content = @file_get_contents($imageFile->tempName);
        if ($content === false) {
            throw new InvalidArgumentException('Failed to read uploaded file');
        }
        $replicateInput['image'] = 'data:' . $mime . ';base64,' . base64_encode($content);

        if ($textureId !== null) {
            $texture = Texture::findOne($textureId);
            if ($texture !== null) {
                $replicateInput['prompt'] = $texture->prompt_suffix;
            }
        }

        $request = $this->requestService->createAndEnqueue($userId, $textureId, $imageFile, $replicateInput);

        return [
            'ok' => true,
            'request_id' => $request->id,
            'status' => $request->status,
        ];
    }
}
