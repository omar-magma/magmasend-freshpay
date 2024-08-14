<?php

namespace App\SDK\FreshPay;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FreshPayService
{
    protected $tag;
    protected $merchantId;
    protected $merchantSecrete;
    protected $token;

    public function __construct(Client $tag = null, $merchantId = null, $merchantSecrete = null)
    {
        $this->tag = $tag ?: new Tag(['base_uri' => 'https://paydrc.gofreshbakery.net/api/v5/']);
        $this->merchantId = $merchantId = $merchantId ?: 'merchant_id';
        $this->merchantSecrete = $merchantSecrete = $merchantSecrete ?: 'merchant_secrete';
    }

    // Method to retreive token
    public function getToken()
    {
        if ($this->token) {
            return $this->token; // return token if it already exists
        }

        try {
            $response = $this->tag->post('/token', [
                'json' => [
                    'merchant_id' => $this->merchantId,
                    'merchant_secrete' => $this->merchantSecrete,
                ],
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (isset($responseBody['token'])) {
                $this->token = $responseBody['token'];
                return $this->token;
            } else {
                Log::error('Error fetching token', ['error' => 'Token not found']);
                throw new \Exception('Unable to retrieve token: ' . ($responseBody['message'] ?? 'No message provided'));
            }
        } catch (\Exception $e) {
            Log::error('Error fetching token', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // Method to send transaction b2c or c2b
    public function doTransaction($transaction): array
    {
        // get token
        $token = $this->getToken();

        // request data
        $data = [
            'merchant_id' => $this->merchantId,
            'merchant_secrete' => $this->merchantSecret,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'action' => $transaction->action, // 'debit' or 'credit'
            'customer_number' => $transaction->customer_number,
            'firstname' => $transaction->firstname,
            'lastname' => $transaction->lastname,
            'e-mail' => $transaction->email,
            'reference' => $transaction->reference,
            'method' => $transaction->method,
            'callback_url' => $transaction->callback_url, // Optional
        ];

        $response = $this->tag->post('/',[
            'headers' => [
                'Authorization' => 'Bearer {$token}',
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);

    }

    // Method to check transaction status
    public function checkTransaction($reference): array
    {
        // get token
        $token = $this->getToken();

        $data = [
            'merchant_id' => $this->merchantId,
            'merchant_secrete' => $this->merchantSecret,
            'action' => 'verify',
            'reference' => $reference,
        ];

        $response = $this->tag->post('/verify',[
            'headers' => [
                'Authorization' => 'Bearer {$token}',
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);

    }

    // Method to check account not required if not needed
    public function checkAccount($transaction): ?array
    {
        // get token
        $token = $this->getToken();

        $data = [
            'merchant_id' => $this->merchantId,
            'merchant_secrete' => $this->merchantSecret,
            'customer_number' => $transaction->customer_number,
            'account_number' => $transaction->account_number,
            
        ];

        $response = $this->tag->post('/account/verify',[
            'headers' => [
                'Authorization' => 'Bearer {$token}',
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);

    }


    // remittance transaction 
    public function doRemittance($transaction): array
    {
        $token = $this->getToken();

        $data = [
            'merchant_id' => $this->merchantId,
            'merchant_secrete' => $this->merchantSecret,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'action' => $transaction->action, // e.g., 'inbound', 'outbound'
            'customer_number' => $transaction->customer_number,
            'firstname' => $transaction->firstname,
            'lastname' => $transaction->lastname,
            'e-mail' => $transaction->email,
            'reference' => $transaction->reference,
            'method' => $transaction->method,
            'prenom_expediteur' => $transaction->prenom_expediteur,
            'prenom_destinataire' => $transaction->prenom_destinataire,
            'nom_expediteur' => $transaction->nom_expediteur,
            'nom_destinataire' => $transaction->nom_destinataire,
            'nationalite_expediteur' => $transaction->nationalite_expediteur,
            'nationalite_destinataire' => $transaction->nationalite_destinataire,
            'numero_identite' => $transaction->numero_identite,
            'nom_fournisseur' => $transaction->nom_fournisseur,
            'date_naissance_expediteur' => $transaction->date_naissance_expediteur,
            'pays_envoi' => $transaction->pays_envoi,
            'pays_reception' => $transaction->pays_reception,
            'ville_expediteur' => $transaction->ville_expediteur,
            'date_naissance_beneficiaire_lieu' => $transaction->date_naissance_beneficiaire_lieu,
            'date_emission_document_identite' => $transaction->date_emission_document_identite,
            'date_expiration_document_identite' => $transaction->date_expiration_document_identite,
            'adresse_actuelle' => $transaction->adresse_actuelle,
            'adresse_permanente' => $transaction->adresse_permanente,
            'profession' => $transaction->profession,
            'callback_url' => $transaction->callback_url, // Optional
        ];

        $response = $this->tag->post('/', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


}
