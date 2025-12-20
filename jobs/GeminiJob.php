<?php

namespace app\jobs;

use app\models\Request;
use app\models\Texture;
use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\queue\JobInterface;

class GeminiJob extends BaseObject implements JobInterface
{
    public int $requestId;
    public string $prompt = '';
    public ?string $color = null;
    public ?int $textureId = null;

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
                throw new \RuntimeException('Failed to update request status: ' . json_encode($request->getFirstErrors()));
            }

            $inputImagePath = Yii::getAlias('@webroot/') . $request->input_image_path;
            if (!is_file($inputImagePath) || !is_readable($inputImagePath)) {
                throw new \RuntimeException("Input image not found: {$inputImagePath}");
            }

            $inputSize = @getimagesize($inputImagePath);
            if ($inputSize === false) {
                throw new \RuntimeException('Failed to read input image size.');
            }
            [$inputWidth, $inputHeight] = $inputSize;

            $textureImagePath = null;
            if ($this->textureId !== null) {
                $texture = Texture::findOne($this->textureId);
                if ($texture !== null && !empty($texture->image_path)) {
                    $textureImagePath = Yii::getAlias('@webroot/') . $texture->image_path;
                    if (!is_file($textureImagePath) || !is_readable($textureImagePath)) {
                        $textureImagePath = null;
                    }
                }
            }

            $prompt = $this->buildPrompt($textureImagePath);

            Yii::info([
                'action' => 'gemini_job_start',
                'request_id' => $this->requestId,
                'color' => $this->color,
                'texture_id' => $this->textureId,
                'has_texture_image' => $textureImagePath !== null,
                'prompt' => mb_substr($prompt, 0, 150),
            ], __METHOD__);

            $geminiAvailable = Yii::$app->has('gemini') && Yii::$app->gemini->isAvailable();
            if (!$geminiAvailable) {
                throw new \RuntimeException('Gemini is not configured');
            }

            $resizedTexturePath = $textureImagePath !== null
                ? $this->resizeTextureToOriginal($textureImagePath, $inputWidth, $inputHeight)
                : null;

            try {
                $result = Yii::$app->gemini->editImage(
                    $inputImagePath,
                    $prompt,
                    $resizedTexturePath,
                    null,
                    [
                        'responseModalities' => ['Image'],
                    ]
                );
            } finally {
                if (is_string($resizedTexturePath) && $resizedTexturePath !== $textureImagePath && is_file($resizedTexturePath)) {
                    @unlink($resizedTexturePath);
                }
            }

            if (($result['success'] ?? false) && !empty($result['image_data'])) {
                $outputDir = Yii::getAlias('@webroot/uploads/requests');
                FileHelper::createDirectory($outputDir);

                $fileName = Yii::$app->gemini->saveImage(
                    $result['image_data'],
                    $outputDir,
                    'out_' . $request->id . '_',
                    $result['content_type'] ?? 'image/png'
                );

                $request->output_image_path = 'uploads/requests/' . $fileName;
                $request->status = Request::STATUS_COMPLETED;
                $request->save();

                Yii::info([
                    'action' => 'gemini_job_completed',
                    'request_id' => $this->requestId,
                    'output_path' => $request->output_image_path,
                ], __METHOD__);

                $this->notifyTelegramCompleted($request);
            } else {
                throw new \RuntimeException('Gemini did not return image: ' . ($result['error'] ?? 'unknown error'));
            }
        } catch (\Throwable $e) {
            Yii::error([
                'action' => 'gemini_job_failed',
                'request_id' => $this->requestId,
                'error' => $e->getMessage(),
            ], __METHOD__);

            $request->status = Request::STATUS_FAILED;
            $request->save();

            $this->notifyTelegramFailed($request);
            throw $e;
        }
    }

    private function buildPrompt(?string $textureImagePath): string
    {
        $hasColor = is_string($this->color) && $this->color !== '';
        $hasTexture = $textureImagePath !== null;

        $base = "Professional interior wall refinishing. Apply new finish ONLY to vertical wall surfaces. ";

        // Описание материала
        if ($hasTexture && $hasColor) {
            $base .= "Use the texture pattern from reference image, tinted with {$this->color}. ";
        } elseif ($hasTexture) {
            $base .= "Match exact texture and color from reference image. ";
        } elseif ($hasColor) {
            $base .= "Apply solid color {$this->color}. ";
        } else {
            $base .= "Apply neutral wall finish. ";
        }

        // Критичные ограничения
        $base .= "STRICT BOUNDARIES: ";
        $base .= "- Keep exact original pixel dimensions (same width and height), no outpainting, no canvas expansion, no zoom ";
        $base .= "- Ceiling: keep 100% original, no finish applied ";
        $base .= "- Crown molding/cornices: preserve completely, stop wall finish at bottom edge ";
        $base .= "- Floor/baseboards: no changes ";
        $base .= "- Windows/doors/frames: mask and preserve ";
        $base .= "- Furniture/objects: do not alter ";
        
        // Зоны применения
        $base .= "TARGET AREAS: ";
        $base .= "- All exposed vertical wall sections from floor to ceiling junction ";
        $base .= "- Wall strips above cabinets/furniture up to ceiling line ";
        $base .= "- Wall sections behind/between furniture ";
        $base .= "- Maintain wall perspective and depth ";
        
        // Качество
        $base .= "QUALITY: ";
        $base .= "- Preserve original lighting, shadows, reflections ";
        $base .= "- Keep texture scale consistent with room perspective ";
        $base .= "- Sharp clean edges at all boundaries ";
        $base .= "- Photorealistic result matching original image quality.";

        return $base;
    }

    private function resizeTextureToOriginal(string $texturePath, int $targetWidth, int $targetHeight): string
    {
        $info = @getimagesize($texturePath);
        if ($info === false || $targetWidth <= 0 || $targetHeight <= 0) {
            return $texturePath;
        }

        $mime = $info['mime'] ?? '';
        $create = match ($mime) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
            default => null,
        };

        if ($create === null || !function_exists($create)) {
            return $texturePath;
        }

        $src = @$create($texturePath);
        if (!$src) {
            return $texturePath;
        }

        $dst = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcW, $srcH);

        $tmpDir = Yii::getAlias('@runtime/texture_tmp');
        FileHelper::createDirectory($tmpDir);
        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . uniqid('texture_', true) . '.png';

        $saved = imagepng($dst, $tmpPath, 6);

        imagedestroy($src);
        imagedestroy($dst);

        return $saved ? $tmpPath : $texturePath;
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
