<?php

namespace App\SDK\FreshPay;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RequestClass implements RequestClassInterface
{

    public string|null $tag;
    private array|null $credentials;

    public function __construct(string|null $tag = null, #[\SensitiveParameter] array|null $credentials = null)
    {
        $this->tag = $tag ?: new Tag(['base_uri' => 'https://api.magmasend.com/']);
        $this->credentials = $credentials ?: json_decode(file_get_contents(__DIR__ . '/credentials.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getBalance($transaction = null): array
    {
        // TODO: customize with the requirement of the parner
        // $defaultClass = new DefaultClass($this->tag, $this->credentials['base_uri']);
        // return $defaultClass->httpRequest('', [], 'post', 'balance');
        try {
            $response = $this->tag->post('/balance', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->credentials['auth']['api_key'],
                ],
                'json' => [
                    'transaction' => $transaction,
                ],
            ]);
    
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Error fetching balance', ['error' => $e->getMessage()]);
            throw $e;
        } 
    }

    public function doTransaction($transaction): array
    {
        // TODO: customize with the requirement of the parner
        // $data = [];
        // $defaultClass = new DefaultClass($this->tag, $this->credentials['base_uri']);
        // return $defaultClass->httpRequest("", $data);
        try{
            $response = $this->tag->post('/transaction', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->credentials['auth']['api_key'],
                ],
                'json' => $transaction,
            ]);
            
            Log::info('Transaction successful', ['response' => $response]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Error processing transaction', ['error' => $e->getMessage()]);
            throw $e; 
        }
    }
        

    public function checkTransaction($transaction): array
    {
        // TODO: customize with the requirement of the parner
        // $defaultClass = new DefaultClass($this->tag, $this->credentials['base_uri']);
        // return $defaultClass->httpRequest('/',
        //     [], 'post', 'check');

        try{
            $response = $this->client->get('/transaction/' . $transactionId, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->credentials['auth']['app_key'],
                ]
            ]);
    
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Error checking transaction', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @throws \JsonException
     */
    public function checkAccount($transaction): ?array
    {
        // TODO: Need to custom this by the response and the error of the partner
        // $transaction->delivery_method == 'AccountTransfer' or 'Payment method'
        $data = [
            'customer_number' => $transaction->customer_number,
            'account_number' => $transaction->account_number,
        ];

        $defaultClass = new DefaultClass($this->tag, $this->credentials['base_uri'],);
        return $defaultClass->httpRequest('', $data, 'post', 'account');
    }
    

    public static function callback(array $postDataArray = null): array
    {
        // TODO: Need to custom this by the response and the callback of the partner
        Log::channel('partner-request')->info('callback', [
            'payload' => $postDataArray,
        ]);
        if ($postDataArray != null) {
            return [
                'status' => "", // required
                'transaction' => null, // required
                'message' => @$postDataArray['transaction_status'] == 1 ?
                    'Transaction successful' : 'Transaction failed', // required
                'data' => [
                    'status' => @$postDataArray['transaction_status'] == 1
                        ? RequestClassInterface::MESSAGE_SUCCESS : RequestClassInterface::MESSAGE_FAILED, // required
                    '3p_reference_id' => @$postDataArray['trans_ref_no'] ?? null, // required
                    'delivery_reference' => @$postDataArray['merchant_trans_ref_no'] ?? null, // required
                ],
                'original_response' => $postDataArray
            ];
        }
        return [
            'status' => 'error',
            "transaction" => null,
            'message' => 'Something went wrong',
            'data' => [],
            'original_response' => $postDataArray
        ];
    }

    //TODO: don't touch this, need to be present
    public function send($transaction, $method): array|null
    {
        try {
            throw_if(!method_exists($this, $method), new \Exception('Method not found'));
            return $this->{$method}($transaction);
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
