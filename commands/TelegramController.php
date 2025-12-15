<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class TelegramController extends Controller
{
    public function actionSetWebhook(?string $url = null)
    {
        $url = $url ?? (Yii::$app->params['telegram_webhook_url'] ?? null);
        if (empty($url)) {
            $this->stderr("telegram_webhook_url is not configured\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $secret = Yii::$app->params['telegram_webhook_secret'] ?? null;
        $result = Yii::$app->telegramService->setWebhook($url, $secret);
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

        return ExitCode::OK;
    }
}
