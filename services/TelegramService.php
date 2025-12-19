<?php

namespace app\services;

use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class TelegramService extends Component
{
    public ?string $token = null;

    private ?Api $api = null;
    private int $timeOut = 120;
    private int $connectTimeOut = 60;
    private int $sendRetries = 3;
    private int $retryDelaySeconds = 2;

    public function init()
    {
        parent::init();

        if ($this->token === null) {
            $this->token = Yii::$app->params['telegram_bot_token'] ?? null;
        }

        if (empty($this->token)) {
            Yii::warning('Telegram bot token is missing. Please configure it.', __METHOD__);
            return;
        }

        $this->api = new Api($this->token);
        $this->api
            ->setTimeOut($this->timeOut)
            ->setConnectTimeOut($this->connectTimeOut);
        $httpClient = (new GuzzleHttpClient())
            ->setTimeOut($this->timeOut)
            ->setConnectTimeOut($this->connectTimeOut);
        $this->api->setHttpClientHandler($httpClient);
    }

    public function getApi(): Api
    {
        if ($this->api === null) {
            throw new InvalidConfigException('Telegram API is not configured.');
        }

        return $this->api;
    }

    public function sendMessage(int $chatId, string $text, ?array $replyMarkup = null): array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        return (array)$this->getApi()->sendMessage($payload);
    }

    public function sendPhoto(int $chatId, string $absoluteFilePath, ?string $caption = null): array
    {
        $payload = [
            'chat_id' => $chatId,
            'photo' => InputFile::create($absoluteFilePath),
        ];

        if ($caption !== null && $caption !== '') {
            $payload['caption'] = $caption;
            $payload['parse_mode'] = 'HTML';
        }

        $attempts = max(1, $this->sendRetries);
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return (array)$this->getApi()->sendPhoto($payload);
            } catch (\Throwable $e) {
                $isTimeout = strpos($e->getMessage(), 'cURL error 28') !== false;
                if (!$isTimeout || $attempt >= $attempts) {
                    throw $e;
                }
                Yii::warning([
                    'step' => 'telegram_send_photo_retry',
                    'attempt' => $attempt,
                    'retries' => $attempts,
                    'reason' => $e->getMessage(),
                ], __METHOD__);
                sleep($this->retryDelaySeconds);
            }
        }

        return [];
    }

    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        $payload = [
            'url' => $url,
        ];

        if (!empty($secretToken)) {
            $payload['secret_token'] = $secretToken;
        }

        return (array)$this->getApi()->setWebhook($payload);
    }
}
