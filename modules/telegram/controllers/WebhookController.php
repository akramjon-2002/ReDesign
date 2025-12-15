<?php

namespace app\modules\telegram\controllers;

use Yii;
use app\services\TelegramUpdateService;
use yii\web\Controller;
use yii\web\Response;

class WebhookController extends Controller
{
    public $enableCsrfValidation = false;

    private TelegramUpdateService $updateService;

    public function __construct($id, $module, TelegramUpdateService $updateService, $config = [])
    {
        $this->updateService = $updateService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;

        $expectedSecret = Yii::$app->params['telegram_webhook_secret'] ?? null;
        if (!empty($expectedSecret)) {
            $givenSecret = $request->headers->get('X-Telegram-Bot-Api-Secret-Token');
            if ($givenSecret !== $expectedSecret) {
                Yii::warning('Invalid telegram webhook secret token', __METHOD__);
                Yii::$app->response->format = Response::FORMAT_RAW;
                Yii::$app->response->statusCode = 403;
                return 'Forbidden';
            }
        }

        $raw = $request->getRawBody();
        if ($raw !== '') {
            Yii::info($raw, __METHOD__);
        }

        $data = json_decode($raw, true);
        if (is_array($data)) {
            try {
                $this->updateService->handle($data);
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
            }
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        return 'OK';
    }
}
