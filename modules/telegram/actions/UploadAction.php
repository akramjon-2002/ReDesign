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
            $maxBytes = 10 * 1024 * 1024;
            $userId = Yii::$app->request->post('user_id');
            if ($userId === null || $userId === '') {
                throw new BadRequestHttpException('user_id is required');
            }

            $textureId = Yii::$app->request->post('texture_id');
            $textureId = ($textureId === null || $textureId === '') ? null : (int)$textureId;

            $color = Yii::$app->request->post('color');
            $color = (is_string($color) && $color !== '') ? $color : null;

            $aspectRatio = Yii::$app->request->post('aspect_ratio');
            $aspectRatio = (is_string($aspectRatio) && $aspectRatio !== '') ? $aspectRatio : null;

            $imageFile = UploadedFile::getInstanceByName('photo');
            if ($imageFile === null) {
                throw new BadRequestHttpException('photo file is required');
            }

            if ($imageFile->error !== UPLOAD_ERR_OK) {
                if ($imageFile->error === UPLOAD_ERR_INI_SIZE || $imageFile->error === UPLOAD_ERR_FORM_SIZE) {
                    $iniMax = ini_get('upload_max_filesize');
                    $postMax = ini_get('post_max_size');
                    throw new BadRequestHttpException(
                        'Лимит загрузки на сервере. upload_max_filesize=' . $iniMax . ', post_max_size=' . $postMax . '.'
                    );
                }
                throw new BadRequestHttpException('Ошибка загрузки файла (код: ' . $imageFile->error . ').');
            }

            if ($imageFile->size > $maxBytes) {
                throw new BadRequestHttpException('Фото больше 10 МБ. Загрузите файл меньше.');
            }

            if ($textureId === null && $color === null) {
                throw new BadRequestHttpException('Please select a texture or color');
            }

            Yii::info([
                'step' => 'upload_action_received',
                'user_id' => $userId,
                'texture_id' => $textureId,
                'color' => $color,
                'aspect_ratio' => $aspectRatio,
                'photo_name' => $imageFile->name,
                'photo_size' => $imageFile->size,
            ], __METHOD__);

            $request = $this->requestService->createAndEnqueueGemini(
                $userId,
                $textureId,
                $imageFile,
                $color,
                $aspectRatio
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
