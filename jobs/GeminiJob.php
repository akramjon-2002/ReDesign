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
                $outputPath = $outputDir . DIRECTORY_SEPARATOR . $fileName;
                $this->logImageSizes($request->id, $inputImagePath, $textureImagePath, $resizedTexturePath, $outputPath);

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

        $base = "Professional interior wall refinishing. Apply new finish to ALL wall surfaces, including the large left wall and the right wall plane (100% coverage, no unedited wall pixels). ";

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

        // Критичные ограничения (коротко и приоритетно)
        $base .= "STRICT BOUNDARIES: ";
        $base .= "- Keep exact original pixel dimensions, no outpainting, no canvas expansion, no zoom ";
        $base .= "- Ceiling and ceiling plinths (crown molding/cornices): keep 100% original, do not recolor or texture; stop finish at the bottom edge of molding ";
        $base .= "- Doors/frames and windows: keep original, do not alter ";
        $base .= "- Do not add any new objects, shapes, stains, or artifacts anywhere on the wall ";
        
        // Зоны применения
        $base .= "TARGET AREAS: ";
        $base .= "- ALL wall planes, especially the large left wall and the right wall plane, from floor/baseboard line up to the molding/ceiling boundary ";
        $base .= "- Include columns/pillars as part of the wall finish ";
        $base .= "- Wall strips above cabinets/furniture up to ceiling line ";
        $base .= "- Wall sections behind/between furniture ";
        $base .= "- Maintain wall perspective and depth; no gaps or missed wall patches; keep clean empty wall areas ";
        
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

    private function logImageSizes(int $requestId, string $inputPath, ?string $texturePath, ?string $resizedTexturePath, ?string $outputPath = null): void
    {
        $logDir = Yii::getAlias('@runtime/logs');
        FileHelper::createDirectory($logDir);

        $inputSize = $this->formatImageSize($inputPath);
        $textureSize = $texturePath ? $this->formatImageSize($texturePath) : 'none';
        $resizedSize = $resizedTexturePath && $resizedTexturePath !== $texturePath
            ? $this->formatImageSize($resizedTexturePath)
            : 'same';
        $outputSize = $outputPath ? $this->formatImageSize($outputPath) : 'pending';

        $line = sprintf(
            "[%s] request_id=%d input=%s texture=%s resized_texture=%s output=%s\n",
            date('c'),
            $requestId,
            $inputSize,
            $textureSize,
            $resizedSize,
            $outputSize
        );
        @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'image_sizes.log', $line, FILE_APPEND);
    }

    private function formatImageSize(string $path): string
    {
        $size = @getimagesize($path);
        if ($size === false) {
            return 'unknown';
        }
        return $size[0] . 'x' . $size[1];
    }

    private function notifyTelegramCompleted(Request $request): void
    {
        // Результат теперь показывается в WebView, не отправляем фото в бот
        Yii::info([
            'action' => 'request_completed_webview',
            'request_id' => $request->id,
            'user_id' => $request->user_id,
            'output_path' => $request->output_image_path,
        ], __METHOD__);
    }

    private function notifyTelegramFailed(Request $request): void
    {
        // Ошибка теперь показывается в WebView, не отправляем сообщение в бот
        Yii::info([
            'action' => 'request_failed_webview',
            'request_id' => $request->id,
            'user_id' => $request->user_id,
        ], __METHOD__);
    }
}
