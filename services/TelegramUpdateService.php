<?php

namespace app\services;

use Yii;
use yii\base\InvalidConfigException;

class TelegramUpdateService
{
    private TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle(array $update): void
    {
        $message = $update['message'] ?? null;
        if (!is_array($message)) {
            return;
        }

        $chat = $message['chat'] ?? null;
        if (!is_array($chat) || !isset($chat['id'])) {
            return;
        }

        $chatId = (int)$chat['id'];
        $text = trim((string)($message['text'] ?? ''));

        if ($text === '/start' || strpos($text, '/start ') === 0) {
            $this->handleStart($chatId);
            return;
        }

        if ($text === '') {
            return;
        }

        $this->telegram->sendMessage($chatId, 'Открой WebApp через кнопку /start');
    }

    private function handleStart(int $chatId): void
    {
        $webappUrl = Yii::$app->params['telegram_webapp_url'] ?? null;
        if (empty($webappUrl)) {
            throw new InvalidConfigException('telegram_webapp_url is not configured. Set TELEGRAM_WEBAPP_URL.');
        }

        // Передаём user_id через query-параметр как fallback (Telegram не всегда передаёт user в initData)
        $separator = (strpos($webappUrl, '?') === false) ? '?' : '&';
        $webappUrlWithUser = $webappUrl . $separator . 'user_id=' . $chatId;

        $replyMarkup = [
            'keyboard' => [
                [
                    [
                        'text' => 'Open WebApp',
                        'web_app' => ['url' => $webappUrlWithUser],
                    ],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];

        $this->telegram->sendMessage(
            $chatId,
            'Загрузи фото комнаты и выбери стиль в WebApp.',
            $replyMarkup
        );
    }
}
