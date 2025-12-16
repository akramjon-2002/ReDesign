<?php

namespace app\services;

use Yii;
use yii\base\Component;
use yii\base\Exception;

class GeminiService extends Component
{
    /**
     * @var string Gemini API Key
     */
    public $apiKey;

    /**
     * @var string Base URL for Gemini API
     */
    public $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * @var string Model name
     */
    public $model = 'gemini-2.5-flash-image';

    public function init()
    {
        parent::init();

        if ($this->apiKey === null) {
            $this->apiKey = Yii::$app->params['gemini_api_key'] ?? getenv('GEMINI_API_KEY');
        }

        if (empty($this->apiKey)) {
            Yii::warning('Gemini API Key is missing. Please configure it.', __METHOD__);
        }
    }

    public function isAvailable(): bool
    {
        return is_string($this->apiKey) && $this->apiKey !== '';
    }

    public function generateImage(string $prompt, ?string $aspectRatio = null, array $generationConfig = []): array
    {
        $parts = [
            ['text' => $prompt],
        ];

        $payload = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
        ];

        $cfg = $this->buildGenerationConfig($aspectRatio, $generationConfig);
        if (!empty($cfg)) {
            $payload['generationConfig'] = $cfg;
        }

        Yii::info([
            'action' => 'gemini_generate_image',
            'model' => $this->model,
            'prompt' => mb_substr($prompt, 0, 120),
            'aspect_ratio' => $aspectRatio,
        ], __METHOD__);

        return $this->generateContent($payload);
    }

    public function editImage(string $imagePath, string $prompt, ?string $referenceImagePath = null, ?string $aspectRatio = null, array $generationConfig = []): array
    {
        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new Exception("Cannot read image: {$imagePath}");
        }

        $parts = [
            ['text' => $prompt],
            [
                'inline_data' => [
                    'mime_type' => $this->detectMimeType($imagePath),
                    'data' => $this->fileToBase64($imagePath),
                ],
            ],
        ];

        if (is_string($referenceImagePath) && $referenceImagePath !== '' && is_file($referenceImagePath) && is_readable($referenceImagePath)) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $this->detectMimeType($referenceImagePath),
                    'data' => $this->fileToBase64($referenceImagePath),
                ],
            ];
        }

        $payload = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
        ];

        $cfg = $this->buildGenerationConfig($aspectRatio, $generationConfig);
        if (!empty($cfg)) {
            $payload['generationConfig'] = $cfg;
        }

        Yii::info([
            'action' => 'gemini_edit_image',
            'model' => $this->model,
            'prompt' => mb_substr($prompt, 0, 120),
            'has_reference' => is_string($referenceImagePath) && $referenceImagePath !== '' && is_file($referenceImagePath),
            'aspect_ratio' => $aspectRatio,
        ], __METHOD__);

        return $this->generateContent($payload);
    }

    public function saveImage(string $imageData, string $outputDir, string $prefix = 'out_', string $contentType = 'image/png'): string
    {
        $extension = match (true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'webp') => 'webp',
            default => 'png',
        };

        if (!is_dir($outputDir)) {
            if (!@mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
                throw new \RuntimeException('Failed to create output directory: ' . $outputDir);
            }
        }

        $fileName = uniqid($prefix, true) . '.' . $extension;
        $path = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

        if (@file_put_contents($path, $imageData) === false) {
            throw new \RuntimeException('Failed to save image to: ' . $path);
        }

        return $fileName;
    }

    private function buildGenerationConfig(?string $aspectRatio, array $generationConfig): array
    {
        $cfg = $generationConfig;

        if (!isset($cfg['responseModalities'])) {
            $cfg['responseModalities'] = ['Image'];
        }

        if (is_string($aspectRatio) && $aspectRatio !== '') {
            $cfg['imageConfig'] = $cfg['imageConfig'] ?? [];
            if (!isset($cfg['imageConfig']['aspectRatio'])) {
                $cfg['imageConfig']['aspectRatio'] = $aspectRatio;
            }
        }

        return $cfg;
    }

    private function generateContent(array $payload): array
    {
        if (!$this->isAvailable()) {
            return ['success' => false, 'image_data' => null, 'error' => 'Gemini API key is missing'];
        }

        $url = rtrim($this->baseUrl, '/') . '/' . $this->model . ':generateContent';
        $response = $this->sendJsonRequest($url, $payload);

        if (!($response['success'] ?? false)) {
            return $response;
        }

        $data = $response['data'] ?? null;
        if (!is_array($data)) {
            return ['success' => false, 'image_data' => null, 'error' => 'Invalid Gemini response'];
        }

        $extracted = $this->extractFirstInlineImage($data);
        if (!($extracted['success'] ?? false)) {
            return $extracted;
        }

        return $extracted;
    }

    private function sendJsonRequest(string $url, array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return ['success' => false, 'data' => null, 'error' => 'Failed to JSON encode request'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            return ['success' => false, 'data' => null, 'error' => 'cURL error: ' . $err];
        }

        if (!is_string($body) || $body === '') {
            return ['success' => false, 'data' => null, 'error' => 'Empty response from Gemini'];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'data' => null, 'error' => 'Invalid JSON from Gemini: HTTP ' . $status];
        }

        if ($status < 200 || $status >= 300) {
            $msg = $decoded['error']['message'] ?? ('HTTP ' . $status);
            return ['success' => false, 'data' => null, 'error' => 'Gemini API error: ' . $msg, 'raw' => $decoded];
        }

        return ['success' => true, 'data' => $decoded, 'error' => null];
    }

    private function extractFirstInlineImage(array $response): array
    {
        $candidates = $response['candidates'] ?? null;
        if (!is_array($candidates) || empty($candidates)) {
            return ['success' => false, 'image_data' => null, 'error' => 'No candidates in Gemini response'];
        }

        foreach ($candidates as $cand) {
            $content = $cand['content'] ?? null;
            $parts = $content['parts'] ?? null;
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                if (!is_array($inline)) {
                    continue;
                }

                $b64 = $inline['data'] ?? null;
                $mime = $inline['mimeType'] ?? $inline['mime_type'] ?? 'image/png';

                if (!is_string($b64) || $b64 === '') {
                    continue;
                }

                $bin = base64_decode($b64, true);
                if ($bin === false) {
                    return ['success' => false, 'image_data' => null, 'error' => 'Failed to decode image base64'];
                }

                return ['success' => true, 'image_data' => $bin, 'content_type' => $mime];
            }
        }

        return ['success' => false, 'image_data' => null, 'error' => 'No inline image found in Gemini response'];
    }

    private function fileToBase64(string $path): string
    {
        $data = @file_get_contents($path);
        if (!is_string($data) || $data === '') {
            throw new Exception('Failed to read file for base64: ' . $path);
        }
        return base64_encode($data);
    }

    private function detectMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }
}
