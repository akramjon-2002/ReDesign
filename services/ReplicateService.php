<?php

namespace app\services;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\base\Exception;

class ReplicateService extends Component
{
    /**
     * @var string Replicate API Token
     */
    public $apiToken;

    /**
     * @var string Base URL for Replicate API
     */
    public $baseUrl = 'https://api.replicate.com/v1';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();
        if ($this->apiToken === null) {
            // Attempt to load from params or environment if not explicitly set
            $this->apiToken = Yii::$app->params['replicate_api_key'] ?? getenv('REPLICATE_API_KEY');
        }

        if (empty($this->apiToken)) {
            Yii::warning('Replicate API Token is missing. Please configure it.', __METHOD__);
        }

        $this->client = new Client([
            'baseUrl' => $this->baseUrl,
            // Using CurlTransport is recommended for better compatibility
            'transport' => 'yii\httpclient\CurlTransport', 
        ]);
    }

    /**
     * Create a prediction (generation request)
     *
     * @param string $versionId The Replicate model version ID
     * @param array $input The input parameters (e.g., ['image' => '...', 'prompt' => '...'])
     * @param string|null $webhookUrl Optional webhook URL for async updates
     * @return array The response data from Replicate
     * @throws Exception
     */
    public function createPrediction(string $versionId, array $input, ?string $webhookUrl = null): array
    {
        $payload = [
            'version' => $versionId,
            'input' => $input,
        ];

        if ($webhookUrl) {
            $payload['webhook'] = $webhookUrl;
            $payload['webhook_events_filter'] = ['completed']; // We generally care about completion
        }

        $request = $this->client->createRequest()
            ->setMethod('POST')
            ->setUrl('predictions')
            ->addHeaders([
                'Authorization' => 'Token ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'User-Agent' => 'Yii2-Interior-Design-Bot',
            ])
            ->setFormat(Client::FORMAT_JSON)
            ->setData($payload);

        $response = $request->send();

        if (!$response->isOk) {
            $this->handleError($response);
        }

        return $response->data;
    }

    /**
     * Check the status of a specific prediction
     *
     * @param string $id The prediction ID
     * @return array The current state of the prediction
     * @throws Exception
     */
    public function checkPredictionStatus(string $id): array
    {
        $request = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl("predictions/{$id}")
            ->addHeaders([
                'Authorization' => 'Token ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ]);

        $response = $request->send();

        if (!$response->isOk) {
            $this->handleError($response);
        }

        return $response->data;
    }

    /**
     * Handle HTTP errors from the API
     *
     * @param \yii\httpclient\Response $response
     * @throws Exception
     */
    protected function handleError($response)
    {
        $statusCode = $response->getStatusCode();
        $content = $response->content;
        $data = $response->data;

        $message = "Replicate API Request Failed ($statusCode)";
        if (isset($data['detail'])) {
            $message .= ": " . (is_array($data['detail']) ? json_encode($data['detail']) : $data['detail']);
        } elseif (isset($data['error'])) {
            $message .= ": " . $data['error'];
        }

        Yii::error("Replicate Service Error: " . $message . "\nResponse: " . $content, __METHOD__);
        throw new Exception($message, $statusCode);
    }
}
