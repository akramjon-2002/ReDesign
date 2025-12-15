<?php

namespace app\services;

use Yii;
use yii\base\Component;
use yii\base\Exception;

class StabilityService extends Component
{
    /**
     * @var string Stability AI API Key
     */
    public $apiKey;

    /**
     * @var string Base URL for Stability AI API
     */
    public $baseUrl = 'https://api.stability.ai/v2beta';

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();
        if ($this->apiKey === null) {
            $this->apiKey = Yii::$app->params['stability_api_key'] ?? getenv('STABILITY_API_KEY');
        }

        if (empty($this->apiKey)) {
            Yii::warning('Stability AI API Key is missing. Please configure it.', __METHOD__);
        }
    }

    /**
     * Generate image using Stability AI Ultra model
     *
     * @param string $prompt Text prompt for generation
     * @param string $aspectRatio Aspect ratio (16:9, 1:1, 21:9, 2:3, 3:2, 4:5, 5:4, 9:16, 9:21)
     * @param string $outputFormat Output format (webp, png, jpeg)
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    public function generateUltra(string $prompt, string $aspectRatio = '1:1', string $outputFormat = 'png'): array
    {
        $url = "{$this->baseUrl}/stable-image/generate/ultra";

        $postFields = [
            'prompt' => $prompt,
            'aspect_ratio' => $aspectRatio,
            'output_format' => $outputFormat,
        ];

        Yii::info([
            'action' => 'stability_generate_ultra',
            'prompt' => mb_substr($prompt, 0, 100),
            'aspect_ratio' => $aspectRatio,
        ], __METHOD__);

        return $this->sendRequest($url, $postFields);
    }

    /**
     * Generate image using Stability AI SD3 model
     *
     * @param string $prompt Text prompt for generation
     * @param string $aspectRatio Aspect ratio
     * @param string $outputFormat Output format (webp, png, jpeg)
     * @param string $model Model version (sd3.5-large, sd3.5-large-turbo, sd3.5-medium, sd3-large, sd3-large-turbo, sd3-medium)
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    public function generateSD3(string $prompt, string $aspectRatio = '1:1', string $outputFormat = 'png', string $model = 'sd3.5-large'): array
    {
        $url = "{$this->baseUrl}/stable-image/generate/sd3";

        $postFields = [
            'prompt' => $prompt,
            'aspect_ratio' => $aspectRatio,
            'output_format' => $outputFormat,
            'model' => $model,
        ];

        Yii::info([
            'action' => 'stability_generate_sd3',
            'prompt' => mb_substr($prompt, 0, 100),
            'model' => $model,
        ], __METHOD__);

        return $this->sendRequest($url, $postFields);
    }

    /**
     * Edit image using inpaint (replace masked area)
     *
     * @param string $imagePath Path to source image
     * @param string $prompt Text prompt describing what to put in masked area
     * @param string|null $maskPath Path to mask image (white = edit area)
     * @param string $outputFormat Output format
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    public function inpaint(string $imagePath, string $prompt, ?string $maskPath = null, string $outputFormat = 'png'): array
    {
        $url = "{$this->baseUrl}/stable-image/edit/inpaint";

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new Exception("Cannot read image: $imagePath");
        }

        $postFields = [
            'image' => new \CURLFile($imagePath),
            'prompt' => $prompt,
            'output_format' => $outputFormat,
        ];

        if ($maskPath !== null && is_file($maskPath)) {
            $postFields['mask'] = new \CURLFile($maskPath);
        }

        Yii::info([
            'action' => 'stability_inpaint',
            'prompt' => mb_substr($prompt, 0, 100),
            'has_mask' => $maskPath !== null,
        ], __METHOD__);

        return $this->sendRequest($url, $postFields, true);
    }

    /**
     * Search and replace objects in image
     *
     * @param string $imagePath Path to source image
     * @param string $prompt What to replace with
     * @param string $searchPrompt What to search for
     * @param string $outputFormat Output format
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    public function searchAndReplace(string $imagePath, string $prompt, string $searchPrompt, string $outputFormat = 'png'): array
    {
        $url = "{$this->baseUrl}/stable-image/edit/search-and-replace";

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new Exception("Cannot read image: $imagePath");
        }

        $postFields = [
            'image' => new \CURLFile($imagePath),
            'prompt' => $prompt,
            'search_prompt' => $searchPrompt,
            'output_format' => $outputFormat,
        ];

        Yii::info([
            'action' => 'stability_search_replace',
            'prompt' => mb_substr($prompt, 0, 100),
            'search_prompt' => mb_substr($searchPrompt, 0, 50),
        ], __METHOD__);

        return $this->sendRequest($url, $postFields, true);
    }

    /**
     * Image-to-image generation
     *
     * @param string $imagePath Path to source image
     * @param string $prompt Text prompt
     * @param float $strength How much to transform (0.0 to 1.0)
     * @param string $outputFormat Output format
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    public function imageToImage(string $imagePath, string $prompt, float $strength = 0.7, string $outputFormat = 'png'): array
    {
        $url = "{$this->baseUrl}/stable-image/generate/sd3";

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new Exception("Cannot read image: $imagePath");
        }

        $postFields = [
            'image' => new \CURLFile($imagePath),
            'prompt' => $prompt,
            'strength' => $strength,
            'output_format' => $outputFormat,
            'mode' => 'image-to-image',
        ];

        Yii::info([
            'action' => 'stability_image_to_image',
            'prompt' => mb_substr($prompt, 0, 100),
            'strength' => $strength,
        ], __METHOD__);

        return $this->sendRequest($url, $postFields, true);
    }

    /**
     * Send request to Stability AI API
     *
     * @param string $url API endpoint
     * @param array $postFields POST fields
     * @param bool $isMultipart Whether request contains files
     * @return array ['success' => bool, 'image_data' => string (binary), 'error' => string]
     * @throws Exception
     */
    protected function sendRequest(string $url, array $postFields, bool $isMultipart = false): array
    {
        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: image/*',
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_TIMEOUT => 180,
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($resp === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: " . $error);
        }
        curl_close($ch);

        $result = [
            'success' => false,
            'image_data' => null,
            'content_type' => $contentType,
            'error' => null,
        ];

        if ($httpCode === 200 && strpos($contentType, 'image/') === 0) {
            $result['success'] = true;
            $result['image_data'] = $resp;
            
            Yii::info([
                'action' => 'stability_success',
                'http_code' => $httpCode,
                'image_size' => strlen($resp),
            ], __METHOD__);
        } else {
            $errorData = json_decode($resp, true);
            $errorMessage = $errorData['message'] ?? $errorData['name'] ?? $resp;
            
            $result['error'] = "Stability API error ($httpCode): $errorMessage";
            
            Yii::error([
                'action' => 'stability_error',
                'http_code' => $httpCode,
                'error' => $errorMessage,
                'response' => mb_substr($resp, 0, 500),
            ], __METHOD__);
        }

        return $result;
    }

    /**
     * Save binary image data to file
     *
     * @param string $imageData Binary image data
     * @param string $outputDir Directory to save the image
     * @param string $prefix File name prefix
     * @param string $contentType Content type to determine extension
     * @return string File name of saved file
     */
    public function saveImage(string $imageData, string $outputDir, string $prefix = 'out_', string $contentType = 'image/png'): string
    {
        $extension = match (true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'webp') => 'webp',
            default => 'png',
        };

        $fileName = $prefix . uniqid('', true) . '.' . $extension;
        $absolutePath = $outputDir . DIRECTORY_SEPARATOR . $fileName;

        file_put_contents($absolutePath, $imageData);

        return $fileName;
    }
}
