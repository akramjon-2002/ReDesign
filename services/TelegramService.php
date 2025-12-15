<?php

namespace app\services;

use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class TelegramService extends Component
{
    public ?string $token = null;

    private ?Api $api = null;

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

        return (array)$this->getApi()->sendPhoto($payload);
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
