<?php

namespace app\jobs;

use app\models\Request;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ReplicateJob extends BaseObject implements JobInterface
{
    public int $requestId;
    public string $versionId;
    public array $input = [];
    public int $checkCount = 0;
    public int $maxChecks = 60;

    public function execute($queue)
    {
        $request = Request::findOne($this->requestId);
        if ($request === null) {
            return;
        }

        if ($request->status === Request::STATUS_COMPLETED || $request->status === Request::STATUS_FAILED) {
            return;
        }

        try {
            if (empty($request->replicate_id)) {
                $request->status = Request::STATUS_PROCESSING;
                if (!$request->save()) {
                    throw new \RuntimeException('Failed to update request status to processing: ' . json_encode($request->getFirstErrors()));
                }

                $response = Yii::$app->replicate->createPrediction($this->versionId, $this->input);
                $replicateId = $response['id'] ?? null;
                if (empty($replicateId)) {
                    throw new \RuntimeException('Replicate did not return prediction id.');
                }

                $request->replicate_id = (string)$replicateId;
                if (!$request->save()) {
                    throw new \RuntimeException('Failed to save replicate_id: ' . json_encode($request->getFirstErrors()));
                }
            }

            $statusResponse = Yii::$app->replicate->checkPredictionStatus($request->replicate_id);
            $status = $statusResponse['status'] ?? null;

            if ($status === 'succeeded') {
                $output = $statusResponse['output'] ?? null;
                $outputPath = $this->saveOutput($output, $request->id);
                $request->output_image_path = $outputPath;
                $request->status = Request::STATUS_COMPLETED;
                $request->save();

                $this->notifyTelegramCompleted($request);
                return;
            }

            if ($status === 'failed' || $status === 'canceled') {
                $request->status = Request::STATUS_FAILED;
                $request->save();

                $this->notifyTelegramFailed($request);
                return;
            }

            $this->checkCount++;
            if ($this->checkCount > $this->maxChecks) {
                $request->status = Request::STATUS_FAILED;
                $request->save();

                $this->notifyTelegramFailed($request);
                return;
            }

            $queue->delay(10)->push(new self([
                'requestId' => $this->requestId,
                'versionId' => $this->versionId,
                'input' => $this->input,
                'checkCount' => $this->checkCount,
                'maxChecks' => $this->maxChecks,
            ]));
        } catch (\Throwable $e) {
            $request->status = Request::STATUS_FAILED;
            $request->save();
            throw $e;
        }
    }

    private function saveOutput($output, int $requestId): ?string
    {
        if (is_array($output)) {
            $output = $output[0] ?? null;
        }

        if (!is_string($output) || $output === '') {
            return null;
        }

        if (!preg_match('~^https?://~i', $output)) {
            return null;
        }

        $client = new \yii\httpclient\Client();
        $response = $client->get($output)->send();
        if (!$response->isOk) {
            return null;
        }

        $dir = Yii::getAlias('@webroot/uploads/requests');
        \yii\helpers\FileHelper::createDirectory($dir);

        $fileName = 'out_' . $requestId . '_' . uniqid('', true) . '.jpg';
        $absolutePath = $dir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($absolutePath, $response->content);

        return 'uploads/requests/' . $fileName;
    }

    private function notifyTelegramCompleted(Request $request): void
    {
        try {
            $chatId = (int)$request->user_id;
            $caption = 'Готово. Request #' . $request->id;

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
            Yii::$app->telegramService->sendMessage($chatId, 'Не удалось сгенерировать результат. Request #' . $request->id);
        } catch (\Throwable $e) {
            Yii::warning($e, __METHOD__);
        }
    }
}
