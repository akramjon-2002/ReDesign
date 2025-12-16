<?php

namespace app\modules\telegram\actions;

use app\models\Texture;
use app\services\RequestService;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    private RequestService $requestService;

    public function __construct($id, $controller, RequestService $requestService, $config = [])
    {
        $this->requestService = $requestService;
        parent::__construct($id, $controller, $config);
    }

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $userId = Yii::$app->request->post('user_id');
            if ($userId === null || $userId === '') {
                throw new BadRequestHttpException('user_id is required');
            }

            $textureId = Yii::$app->request->post('texture_id');
            $textureId = ($textureId === null || $textureId === '') ? null : (int)$textureId;

            $imageFile = UploadedFile::getInstanceByName('photo');
            if ($imageFile === null) {
                throw new BadRequestHttpException('photo file is required');
            }

            Yii::info([
                'step' => 'upload_action_received',
                'user_id' => $userId,
                'texture_id' => $textureId,
                'photo_name' => $imageFile->name,
                'photo_type' => $imageFile->type,
                'photo_size' => $imageFile->size,
            ], __METHOD__);

            $texturePrompt = '';
            $textureTitle = null;
            $textureImagePath = null;
            if ($textureId !== null) {
                $texture = Texture::findOne($textureId);
                if ($texture !== null && !empty($texture->prompt_suffix)) {
                    $texturePrompt = $texture->prompt_suffix;
                }
                if ($texture !== null) {
                    $textureTitle = $texture->title ?? null;
                    $textureImagePath = $texture->image_path ?? null;
                }
            }

            if ($texturePrompt === '') {
                $texturePrompt = $textureTitle ?: 'wallpaper texture';
            }

            $autoDesc = $this->buildAutoTextureDescription($textureImagePath);
            if ($autoDesc !== null && $autoDesc !== '') {
                Yii::info([
                    'step' => 'texture_auto_desc',
                    'texture_id' => $textureId,
                    'texture_image_path' => $textureImagePath,
                    'auto_desc' => $autoDesc,
                ], __METHOD__);
                $texturePrompt = trim($texturePrompt . ', ' . $autoDesc);
            }

            $prompt = "Repaint the walls to {$texturePrompt}. Keep the room layout exactly the same. Do not add or remove any furniture or objects. Preserve the original photo composition.";

            Yii::info([
                'action' => 'upload_for_stability',
                'user_id' => $userId,
                'texture_id' => $textureId,
                'texture_title' => $textureTitle,
                'texture_image_path' => $textureImagePath,
                'texture_prompt' => $texturePrompt,
                'prompt' => $prompt,
            ], __METHOD__);

            $request = $this->requestService->createAndEnqueueStability(
                $userId,
                $textureId,
                $imageFile,
                $prompt,
                'wall, walls, all wall surfaces, interior walls',
                'auto-wall-inpaint'
            );

            return [
                'ok' => true,
                'request_id' => $request->id,
                'status' => $request->status,
            ];
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function buildAutoTextureDescription(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagecolorat')) {
            return null;
        }

        $absolute = Yii::getAlias('@webroot/') . ltrim($relativePath, '/\\');
        if (!is_file($absolute) || !is_readable($absolute)) {
            return null;
        }

        $src = $this->gdLoadImage($absolute);
        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($src);
            return null;
        }

        $tw = 128;
        $th = 128;
        $img = imagecreatetruecolor($tw, $th);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
        imagedestroy($src);

        $sumR = 0.0;
        $sumG = 0.0;
        $sumB = 0.0;
        $sumL = 0.0;
        $sumL2 = 0.0;
        $n = 0;
        $edge = 0;

        for ($y = 0; $y < $th; $y++) {
            for ($x = 0; $x < $tw; $x++) {
                $col = imagecolorat($img, $x, $y);
                $r = ($col >> 16) & 0xFF;
                $g = ($col >> 8) & 0xFF;
                $b = $col & 0xFF;

                $sumR += $r;
                $sumG += $g;
                $sumB += $b;

                $l = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                $sumL += $l;
                $sumL2 += ($l * $l);
                $n++;

                if ($x + 1 < $tw) {
                    $c2 = imagecolorat($img, $x + 1, $y);
                    $r2 = ($c2 >> 16) & 0xFF;
                    $g2 = ($c2 >> 8) & 0xFF;
                    $b2 = $c2 & 0xFF;
                    $l2 = 0.299 * $r2 + 0.587 * $g2 + 0.114 * $b2;
                    if (abs($l2 - $l) > 18) {
                        $edge++;
                    }
                }
            }
        }

        imagedestroy($img);

        if ($n <= 0) {
            return null;
        }

        $avgR = (int)round($sumR / $n);
        $avgG = (int)round($sumG / $n);
        $avgB = (int)round($sumB / $n);
        $meanL = $sumL / $n;
        $varL = ($sumL2 / $n) - ($meanL * $meanL);
        $stdL = $varL > 0 ? sqrt($varL) : 0.0;
        $edgeRatio = ($tw * $th) > 0 ? ($edge / ($tw * $th)) : 0.0;

        $colorName = $this->approxColorName($avgR, $avgG, $avgB);
        $material = $this->approxMaterial($stdL, $edgeRatio);

        return trim($colorName . ' ' . $material . ', matte, seamless, no geometric pattern, no tiles');
    }

    private function approxMaterial(float $stdL, float $edgeRatio): string
    {
        if ($stdL < 10 && $edgeRatio < 0.08) {
            return 'smooth painted wall';
        }
        if ($stdL < 18 && $edgeRatio < 0.12) {
            return 'fine-grain plaster stucco texture';
        }
        if ($stdL < 30) {
            return 'rough stucco plaster texture';
        }

        return 'highly textured rough plaster';
    }

    private function approxColorName(int $r, int $g, int $b): string
    {
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $s = ($max == $min) ? 0 : (($l > 127) ? ($max - $min) / (510 - $max - $min) : ($max - $min) / ($max + $min));

        if ($r > $g + 40 && $r > $b + 40) {
            if ($l < 80) return 'dark red, deep burgundy, maroon';
            if ($l < 120) return 'rich red, deep red';
            if ($l < 160) return 'red, medium red';
            return 'light red, salmon';
        }
        if ($g > $r + 30 && $g > $b + 30) {
            if ($l < 100) return 'dark green, forest green';
            if ($l < 160) return 'green, medium green';
            return 'light green, mint';
        }
        if ($b > $r + 30 && $b > $g + 30) {
            if ($l < 100) return 'dark blue, navy blue';
            if ($l < 160) return 'blue, medium blue';
            return 'light blue, sky blue';
        }
        if ($r > 180 && $g > 150 && $b < 100) {
            return 'orange, warm orange';
        }
        if ($r > 180 && $g > 180 && $b < 100) {
            return 'yellow, warm yellow';
        }
        if ($r > 210 && $g > 200 && $b > 190) {
            return 'off-white, cream white';
        }
        if ($r > 190 && $g > 170 && $b > 130) {
            return 'warm beige, tan';
        }
        if ($r > 160 && $g > 150 && $b > 140) {
            return 'light gray, silver gray';
        }
        if ($r > 120 && $g > 110 && $b > 90) {
            return 'sand beige, taupe';
        }
        if ($l < 50) {
            return 'very dark, charcoal, almost black';
        }
        if ($l < 90) {
            return 'dark gray, charcoal gray';
        }

        return 'neutral muted color';
    }

    private function gdLoadImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : null,
            default => @imagecreatefromjpeg($path),
        };
    }
}
