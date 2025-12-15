<?php

namespace app\jobs;

use app\models\Request;
use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\queue\JobInterface;

class StabilityJob extends BaseObject implements JobInterface
{
    public int $requestId;
    public string $prompt;
    public string $mode = 'search-and-replace';
    public string $searchPrompt = 'wall';

    public function execute($queue)
    {
        $request = Request::findOne($this->requestId);
        if ($request === null) {
            Yii::warning("Request #{$this->requestId} not found", __METHOD__);
            return;
        }

        if ($request->status === Request::STATUS_COMPLETED || $request->status === Request::STATUS_FAILED) {
            return;
        }

        try {
            $request->status = Request::STATUS_PROCESSING;
            if (!$request->save()) {
                throw new \RuntimeException('Failed to update request status to processing: ' . json_encode($request->getFirstErrors()));
            }

            $inputImagePath = Yii::getAlias('@webroot/') . $request->input_image_path;
            if (!is_file($inputImagePath) || !is_readable($inputImagePath)) {
                throw new \RuntimeException("Input image not found: {$inputImagePath}");
            }

            Yii::info([
                'action' => 'stability_job_start',
                'request_id' => $this->requestId,
                'mode' => $this->mode,
                'prompt' => mb_substr($this->prompt, 0, 100),
            ], __METHOD__);

            $result = match ($this->mode) {
                'search-and-replace' => Yii::$app->stability->searchAndReplace(
                    $inputImagePath,
                    $this->prompt,
                    $this->searchPrompt
                ),
                'image-to-image' => Yii::$app->stability->imageToImage(
                    $inputImagePath,
                    $this->prompt,
                    0.7
                ),
                default => Yii::$app->stability->searchAndReplace(
                    $inputImagePath,
                    $this->prompt,
                    $this->searchPrompt
                ),
            };

            if ($result['success'] && !empty($result['image_data'])) {
                $outputDir = Yii::getAlias('@webroot/uploads/requests');
                FileHelper::createDirectory($outputDir);

                $fileName = Yii::$app->stability->saveImage(
                    $result['image_data'],
                    $outputDir,
                    'out_' . $request->id . '_',
                    $result['content_type'] ?? 'image/png'
                );

                $request->output_image_path = 'uploads/requests/' . $fileName;
                $request->status = Request::STATUS_COMPLETED;
                $request->save();

                Yii::info([
                    'action' => 'stability_job_completed',
                    'request_id' => $this->requestId,
                    'output_path' => $request->output_image_path,
                ], __METHOD__);

                $this->notifyTelegramCompleted($request);
            } else {
                throw new \RuntimeException('Stability AI did not return image: ' . ($result['error'] ?? 'unknown error'));
            }
        } catch (\Throwable $e) {
            Yii::error([
                'action' => 'stability_job_failed',
                'request_id' => $this->requestId,
                'error' => $e->getMessage(),
            ], __METHOD__);

            $request->status = Request::STATUS_FAILED;
            $request->save();

            $this->notifyTelegramFailed($request);
            throw $e;
        }
    }

    private function notifyTelegramCompleted(Request $request): void
    {
        try {
            $chatId = (int)$request->user_id;
            $caption = '✅ Готово! Request #' . $request->id;

            if (!empty($request->output_image_path)) {
                $absolute = Yii::getAlias('@webroot/') . $request->output_image_path;
                if (is_file($absolute) && is_readable($absolute)) {
                    Yii::$app->telegramService->sendPhoto($chatId, $absolute, $caption);
                    return;
                }
            }

            Yii::$app->telegramService->sendMessage($chatId, $caption);
        } catch (\Throwable $e) {
            Yii::warning($e, __METHOD__);
        }
    }

    private function notifyTelegramFailed(Request $request): void
    {
        try {
            $chatId = (int)$request->user_id;
            Yii::$app->telegramService->sendMessage($chatId, '❌ Не удалось сгенерировать результат. Request #' . $request->id);
        } catch (\Throwable $e) {
            Yii::warning($e, __METHOD__);
        }
    }
}
