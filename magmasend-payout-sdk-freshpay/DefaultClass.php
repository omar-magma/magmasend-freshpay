<?php

namespace App\SDK\FreshPay; // TODO: Replace Boilerplate by the name of Sdk for example namespace App\SDK\Fincra

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class DefaultClass
{

    private $config = [];

    private string $baseUrl;

    private ?string $tag;

    public function __construct($tag, $baseUrl)
    {
        $baseConfig['timeout'] = 90;
        $baseConfig['cookies'] = true;
        $this->baseUrl = $baseUrl;
        $baseConfig['headers'] = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        $baseConfig['base_uri'] = sprintf('%s', $baseUrl);
        $this->tag = $tag;
        $this->setConfig($baseConfig);
    }

    public function setConfig($config)
    {
        foreach ($config as $item => $value) {
            $this->config[$item] = $value;
        }
        return $this;
    }

    public function getClient()
    {
        return new Client($this->config);
    }


    /**
     * Make an HTTP request to the Magma Send API.
     *
     * @param string $url The endpoint URL to which the request is made.
     * @param array $data The data to be sent with the request.
     * @param string $method The HTTP method to be used for the request (default is 'post').
     *
     * @return ?array The response data from the HTTP request.
     */
    public function httpRequest(string $url, $data = [], $method = 'post', $action = 'do'): ?array
    {
        try {
            // TODO: Need to custom this by the response and the error of the partner
            Log::channel('partner-request')->info($this->tag . $url . ' ' . $method . ' [start]', [
                'payload' => $data,
                'url' => $url,
                'method' => $method,
                'action' => $action,
            ]);
            if ($action == 'do' && @$data['payment_mode'] == "BAT" && @$data['bank_code'] == null) {
                return [
                    'status' => RequestClassInterface::STATUS_FAILED,
                    'message' => 'Unavailable bank',
                    'data' => [],
                    'original_response' => null
                ];
            }
            if ($action == 'account' && @$data['bank_code'] == null && @$data['network'] == null) {
                return null;
            }

            $responseRequest = $this->request($url, $data, $method);

            Log::channel('partner-request')->info($this->tag . $url . ' ' . $method . ' [middle] responseRequest', [
                'responseRequest' => $responseRequest,
            ]);
            $response = match ($action) {
                'check' => $this->getResponseCheck($responseRequest, $data),
                'balance' => $this->getResponseBalance($responseRequest),
                'account' => $this->getResponseAccount($responseRequest),
                default => $this->getResponseDo($responseRequest, $data),
            };
            Log::channel('partner-request')->info($this->tag . $url . ' ' . $method . ' [end] success', [
                'payload' => $response,
            ]);

        } catch (\Throwable $e) {
            // TODO: Need to custom this by the response and the error of the partner
            Log::channel('partner-request')->info($this->tag . $url . ' ' . $method . ' [end] error', compact('e'));

            $jsonResponse = json_decode($e->getMessage(), true);
            $status = Tools::getStatusAndMessage($jsonResponse);
            $response = [
                'status' => $status['status'],
                'message' => $status['message'],
                'data' => [],
                'original_response' => $jsonResponse
            ];
            Log::channel('partner-request')->info($this->tag . $url . ' ' . $method . ' [end] error', [
                'payload' => $response,
            ]);
        }
        return $response;
    }

    /**
     * Perform an HTTP request.
     *
     * @param string $url The endpoint URL to which the request is made.
     * @param array $payload The data to be sent with the request.
     * @param string $method The HTTP method to be used for the request (default is 'post').
     *
     * @return array The JSON response from the HTTP request.
     *
     * @throws \Exception If the response code in the JSON is not 'CO_SUBMITTED'.
     */
    private function request(string $url, array $payload, string $method = 'post'): array
    {

        try {
            $jsonResponse = match ($method) {
                'post' => (function () use ($url, $payload) {
                    $response = $this->getClient()->post($url, ['json' => $payload]);
                    return json_decode($response->getBody(), true);
                })(),
                'get' => (function () use ($url, $payload) {
                    $response = $this->getClient()->get($url);
                    return json_decode($response->getBody(), true);
                })(),
                default => (function () use ($url, $payload) {
                    $response = $this->getClient()->get($url);
                    return json_decode($response->getBody(), true);
                })(),
            };
        } catch (RequestException $e) {
            // TODO: Need to custom this by the response and the error of the partner
            Log::channel('partner-request')->error($this->tag . $url . ' ' . $method . ' [end] error throw' . $e->getMessage(),
                [
                    'payload' => $payload,
                ]);
            $jsonResponse = json_decode($e->getResponse()->getBody(), true);
            if (isset($jsonResponse['Status']) && $jsonResponse['Status'] != 'Success') {
                Log::channel('partner-request')->error($this->tag . $url . ' ' . $method . ' [end] error catch', compact('e'));
                throw new \Exception(json_encode($jsonResponse));
            } else {
                Log::channel('partner-request')->error($this->tag . $url . ' ' . $method . ' [end] error catch unknown ' . $e->getMessage(), [
                    'payload' => $jsonResponse
                ]);
                $jsonResponse = ['status_code' => 999, 'status_message' => 'An unknown error has occurred'];
                throw new \Exception(json_encode($jsonResponse));
            }
        }

        return $jsonResponse;
    }


    private function getResponseDo(array $response, $payload): array
    {
        $status = Tools::getStatusAndMessage($response);
        $reference = @$response['trans_ref_no'] ?? null;
        return [
            'status' => 'ok',
            'message' => $status['message'],
            'data' => [
                'status' => $status['status'],
                '3p_reference_id' => $reference,
                'delivery_reference' => @$payload->merch_trans_ref_no ?? null,
            ],
            'original_response' => $response
        ];
    }

    // TODO: Change
    private function getResponseCheck(array $response, $payload): array
    {
        return [
            'status' => $response['Status'] ?? 'failed',
            'message' => $response['Trans_Status_Description'] ?? 'No message provided',
            'data' => [
                'status' => $response['Trans_Status'] ?? 'failed',
                '3p_reference_id' => $response['Transaction_id'] ?? 'unknown',
                'delivery_reference' => $payload['reference'] ?? 'unknown',
            ],
            'original_response' => $response
        ];
    }

    // TODO: Change
    private function getResponseBalance(array $response): array
    {
        return [
            'status' => $response['Status'] ?? 'failed',
            'message' => $response['Comment'] ?? 'No message provided',
            'balance' => [
                'amount' => $response['Amount'] ?? 0,
                'currency' => $response['Currency'] ?? 'unknown'
            ],
            'original_response' => $response
        ];
    }

    // TODO: Change
    private function getResponseAccount(array $response): null|array
    {
        // TODO: if verify success show or return null
        if(isset($response['status']) && $response['status'] == 'success') {
            return false ? null : [
                'status' => 'ok',
                'message' => RequestClassInterface::MESSAGE_SUCCESS,
                'data' => [
                    'name' => "name",
                    'reference' => null,
                ],
                'original_response' => $response,
            ];
        }
        return null;
    }

    private function getStatusAndMessage(array $response): array{
        return [
            'status' => $response['status'] ?? 'failed',
            'message' => $response['message'] ?? 'No message provided',
        ];
    }
}
