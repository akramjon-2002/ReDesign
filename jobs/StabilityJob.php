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
    public string $mode = 'auto-wall-inpaint';
    public string $searchPrompt = 'wall, walls, all wall surfaces, interior walls';
    public string $negativePrompt = 'poster, frame, picture, painting, portrait, artwork, photo on wall, mural, graffiti, text, letters, logo, person, face, character, tv, screen, monitor, sofa, couch, furniture, table, chair, plant, vase, lamp, decoration, rug, carpet, bookshelf, bed, wardrobe, curtains';

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
                'search_prompt' => $this->searchPrompt,
                'negative_prompt' => $this->negativePrompt,
                'gd_loaded' => extension_loaded('gd'),
            ], __METHOD__);

            $maskPath = null;
            try {
                $result = match ($this->mode) {
                    'auto-wall-inpaint' => $this->runAutoWallInpaint($inputImagePath, $maskPath),
                    'inpaint' => Yii::$app->stability->inpaint($inputImagePath, $this->prompt, null, $this->negativePrompt),
                    'image-to-image' => Yii::$app->stability->imageToImage($inputImagePath, $this->prompt, 0.7),
                    'search-and-replace' => Yii::$app->stability->searchAndReplace($inputImagePath, $this->prompt, $this->searchPrompt, $this->negativePrompt),
                    default => Yii::$app->stability->searchAndReplace($inputImagePath, $this->prompt, $this->searchPrompt, $this->negativePrompt),
                };
            } finally {
                if (is_string($maskPath) && is_file($maskPath)) {
                    @unlink($maskPath);
                }
            }

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

    private function runAutoWallInpaint(string $inputImagePath, ?string &$maskPath): array
    {
        Yii::info([
            'step' => 'auto_wall_inpaint_start',
            'request_id' => $this->requestId,
        ], __METHOD__);

        $maskInfo = $this->buildAutoWallMaskWithRatio($inputImagePath);
        $maskPath = $maskInfo['path'] ?? null;
        $filledRatio = $maskInfo['filled_ratio'] ?? 0.0;

        if (is_string($maskPath) && $maskPath !== '' && is_file($maskPath)) {
            Yii::info([
                'action' => 'auto_wall_mask_ready',
                'request_id' => $this->requestId,
                'mask_path' => $maskPath,
                'mask_size' => filesize($maskPath) ?: null,
                'filled_ratio' => $filledRatio,
            ], __METHOD__);

            if ($filledRatio > 0.35) {
                Yii::info([
                    'action' => 'mask_too_large_use_search_replace',
                    'request_id' => $this->requestId,
                    'filled_ratio' => $filledRatio,
                ], __METHOD__);
                @unlink($maskPath);
                $maskPath = null;
                return Yii::$app->stability->searchAndReplace($inputImagePath, $this->prompt, $this->searchPrompt, $this->negativePrompt);
            }

            return Yii::$app->stability->inpaint($inputImagePath, $this->prompt, $maskPath, $this->negativePrompt);
        }

        Yii::warning([
            'action' => 'auto_wall_mask_failed_fallback',
            'request_id' => $this->requestId,
        ], __METHOD__);

        return Yii::$app->stability->searchAndReplace($inputImagePath, $this->prompt, $this->searchPrompt, $this->negativePrompt);
    }

    /**
     * Простая авто-маска стен (без нейросегментации): flood fill от нескольких seed-точек
     * в верхней части кадра. Белое = менять, чёрное = не менять.
     * @return array{path: string|null, filled_ratio: float}
     */
    private function buildAutoWallMaskWithRatio(string $imagePath): array
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagecolorat')) {
            return ['path' => null, 'filled_ratio' => 0.0];
        }

        $src = $this->gdLoadImage($imagePath);
        if (!$src) {
            return ['path' => null, 'filled_ratio' => 0.0];
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($src);
            return ['path' => null, 'filled_ratio' => 0.0];
        }

        $scale = min(320 / $w, 320 / $h, 1.0);
        $tw = max(1, (int)round($w * $scale));
        $th = max(1, (int)round($h * $scale));

        $img = imagecreatetruecolor($tw, $th);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
        imagedestroy($src);

        $mask = imagecreatetruecolor($tw, $th);
        $black = imagecolorallocate($mask, 0, 0, 0);
        $white = imagecolorallocate($mask, 255, 255, 255);
        imagefilledrectangle($mask, 0, 0, $tw - 1, $th - 1, $black);

        $candidates = [
            [(int)round($tw * 0.25), (int)round($th * 0.50)],
            [(int)round($tw * 0.50), (int)round($th * 0.50)],
            [(int)round($tw * 0.75), (int)round($th * 0.50)],
            [(int)round($tw * 0.33), (int)round($th * 0.35)],
            [(int)round($tw * 0.66), (int)round($th * 0.35)],
        ];

        $best = null;
        $bestVar = null;
        $scored = [];
        foreach ($candidates as $pt) {
            [$sx, $sy] = $pt;
            $var = $this->localVariance($img, $sx, $sy, 8);
            $scored[] = ['x' => $sx, 'y' => $sy, 'var' => $var];
        }
        usort($scored, static fn($a, $b) => ($a['var'] <=> $b['var']));

        if (!$scored) {
            imagedestroy($img);
            imagedestroy($mask);
            return ['path' => null, 'filled_ratio' => 0.0];
        }

        $seeds = [];
        $minSeedDist = max(10, (int)round($tw * 0.18));
        foreach ($scored as $s) {
            $ok = true;
            foreach ($seeds as $picked) {
                $dx = $s['x'] - $picked['x'];
                $dy = $s['y'] - $picked['y'];
                if (($dx * $dx + $dy * $dy) < ($minSeedDist * $minSeedDist)) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $seeds[] = $s;
            }
            if (count($seeds) >= 3) {
                break;
            }
        }

        $maxDist = 50.0;
        $minY = (int)round($th * 0.12);
        $maxY = (int)round($th * 0.88);

        foreach ($seeds as $s) {
            $this->floodFillAppend($img, $mask, (int)$s['x'], (int)$s['y'], $minY, $maxY, $maxDist, $white);
        }

        $filled = $this->countMaskFilled($mask, $tw, $th, $black);
        $filledRatio = ($tw * $th) > 0 ? ($filled / ($tw * $th)) : null;

        imagedestroy($img);

        if ($filled < 500) {
            imagedestroy($mask);
            return ['path' => null, 'filled_ratio' => $filledRatio ?? 0.0];
        }

        Yii::info([
            'step' => 'auto_wall_mask_stats',
            'request_id' => $this->requestId,
            'filled' => $filled,
            'thumb_w' => $tw,
            'thumb_h' => $th,
            'filled_ratio' => $filledRatio,
            'seeds_used' => array_map(static fn($s) => [$s['x'], $s['y']], $seeds),
        ], __METHOD__);

        $maskFull = imagecreatetruecolor($w, $h);
        imagecopyresampled($maskFull, $mask, 0, 0, 0, 0, $w, $h, $tw, $th);
        imagedestroy($mask);

        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wall_mask_' . $this->requestId . '_' . uniqid('', true) . '.png';
        imagepng($maskFull, $tmp);
        imagedestroy($maskFull);

        return ['path' => $tmp, 'filled_ratio' => $filledRatio ?? 0.0];
    }

    private function floodFillAppend($img, $mask, int $seedX, int $seedY, int $minY, int $maxY, float $maxDist, int $white): void
    {
        $tw = imagesx($img);
        $th = imagesy($img);
        if ($seedX < 0 || $seedY < 0 || $seedX >= $tw || $seedY >= $th) {
            return;
        }

        $seedColor = imagecolorat($img, $seedX, $seedY);
        $sr = ($seedColor >> 16) & 0xFF;
        $sg = ($seedColor >> 8) & 0xFF;
        $sb = $seedColor & 0xFF;

        $visited = array_fill(0, $tw * $th, false);
        $queue = [[$seedX, $seedY]];
        $visited[$seedY * $tw + $seedX] = true;
        $filled = 0;

        while ($queue) {
            [$x, $y] = array_pop($queue);
            if ($y < $minY || $y > $maxY) {
                continue;
            }

            $col = imagecolorat($img, $x, $y);
            $r = ($col >> 16) & 0xFF;
            $g = ($col >> 8) & 0xFF;
            $b = $col & 0xFF;

            $dist = sqrt(($r - $sr) * ($r - $sr) + ($g - $sg) * ($g - $sg) + ($b - $sb) * ($b - $sb));
            if ($dist > $maxDist) {
                continue;
            }

            imagesetpixel($mask, $x, $y, $white);
            $filled++;

            $neighbors = [[$x + 1, $y], [$x - 1, $y], [$x, $y + 1], [$x, $y - 1]];
            foreach ($neighbors as $n) {
                [$nx, $ny] = $n;
                if ($nx < 0 || $ny < 0 || $nx >= $tw || $ny >= $th) {
                    continue;
                }
                $idx = $ny * $tw + $nx;
                if ($visited[$idx]) {
                    continue;
                }
                $visited[$idx] = true;
                $queue[] = [$nx, $ny];
            }

            if ($filled > ($tw * $th * 0.40)) {
                break;
            }
        }
    }

    private function countMaskFilled($mask, int $tw, int $th, int $black): int
    {
        $filled = 0;
        for ($y = 0; $y < $th; $y++) {
            for ($x = 0; $x < $tw; $x++) {
                if (imagecolorat($mask, $x, $y) !== $black) {
                    $filled++;
                }
            }
        }
        return $filled;
    }

    private function gdLoadImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => @imagecreatefromjpeg($path),
        };
    }

    private function localVariance($img, int $x, int $y, int $radius): float
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $x0 = max(0, $x - $radius);
        $y0 = max(0, $y - $radius);
        $x1 = min($w - 1, $x + $radius);
        $y1 = min($h - 1, $y + $radius);

        $count = 0;
        $sum = 0.0;
        $sum2 = 0.0;

        for ($yy = $y0; $yy <= $y1; $yy++) {
            for ($xx = $x0; $xx <= $x1; $xx++) {
                $col = imagecolorat($img, $xx, $yy);
                $r = ($col >> 16) & 0xFF;
                $g = ($col >> 8) & 0xFF;
                $b = $col & 0xFF;
                $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $sum += $lum;
                $sum2 += $lum * $lum;
                $count++;
            }
        }

        if ($count <= 1) return 999999.0;
        $mean = $sum / $count;
        $var = ($sum2 / $count) - ($mean * $mean);
        return $var;
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
