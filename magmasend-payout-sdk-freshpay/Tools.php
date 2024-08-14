<?php

namespace App\SDK\FreshPay;

class Tools
{
    // TODO: customize with the code of the parner

    const MESSAGES_STATUS = [
        200 => ['status' => RequestClassInterface::STATUS_SUCCESS, 'message' => 'Transaction Received Successfully'],
        400 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "An error occurred during the execution of the request"],
        401 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "You cannot make a payment of this amount"],
        402 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "Your balance is insufficient to make this payment"],
        404 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "The transaction identifier is not recognized in the system"],
        405 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "The country flag in the provided phone number is not allowed"],
        407 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "The provided currency value is incorrect or unrecognized"],
        408 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "The specified action is not recognized by the system"],
        409 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "Connectivity problem with the database"],
        500 => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "Internal server error"],
    ];

    const GET_TRANSACTION_STATUS = [
        'Success' => ['status' => RequestClassInterface::STATUS_SUCCESS, 'message' => "Transaction successful"],
        'Pending' => ['status' => RequestClassInterface::STATUS_PENDING, 'message' => "Transaction is pending"],
        'Failed' => ['status' => RequestClassInterface::STATUS_FAILED, 'message' => "Transaction failed"],
    ];

    public static function getStatusAndMessage($response): array
    {
        $statusPartner = $response['resultCode'] ?? $response['status_code'] ?? 500;
        $messagePartner = $response['resultCodeDescription'] ?? $response['status_message'] ?? 'Unknown error';
        $status = RequestClassInterface::STATUS_FAILED;
        $finalMessage = $messagePartner;

        if (isset(self::MESSAGES_STATUS[$statusPartner])) {
            $status = self::MESSAGES_STATUS[$statusPartner]['status'];
            $finalMessage = self::MESSAGES_STATUS[$statusPartner]['message'] ?? $messagePartner;
        }

        return [
            'status' => $status,
            'message' => $finalMessage,
        ];
    }

    public static function getTransactionStatusAndMessage($response): array
    {
        $statusPartner = $response['Status'] ?? 'Failed';
        $status = RequestClassInterface::STATUS_FAILED;
        $messagePartner = $response['Status_Description'] ?? 'Unknown error';
        $finalMessage = $messagePartner;

        if (isset(self::GET_TRANSACTION_STATUS[$statusPartner])) {
            $status = self::GET_TRANSACTION_STATUS[$statusPartner]['status'];
            $finalMessage = self::GET_TRANSACTION_STATUS[$statusPartner]['message'] ?? $messagePartner;
        }

        return [
            'status' => $status,
            'message' => $finalMessage,
        ];
    }

    public static function getMessageOnTransaction(int|null $code): string|null
    {
        return match ($code) {
            200 => "Transaction received successfully",
            400 => 'An error occurred during the execution of the request',
            401 => 'You cannot make a payment of this amount',
            402 => 'Your balance is insufficient to make this payment',
            404 => 'The transaction identifier is not recognized in the system',
            405 => 'The country flag in the provided phone number is not allowed',
            407 => 'The provided currency value is incorrect or unrecognized',
            408 => 'The specified action is not recognized by the system',
            409 => 'Connectivity problem with the database',
            500 => 'Internal server error',
            default => RequestClassInterface::STATUS_FAILED,
        };
    }

    public static function getStatusOnTransaction(int|null $code): string|null
    {
        return match ($code) {
            200 => RequestClassInterface::STATUS_SUCCESS,
            400 => RequestClassInterface::STATUS_FAILED,
            401 => RequestClassInterface::STATUS_FAILED,
            402 => RequestClassInterface::STATUS_FAILED,
            404 => RequestClassInterface::STATUS_FAILED,
            405 => RequestClassInterface::STATUS_FAILED,
            407 => RequestClassInterface::STATUS_FAILED,
            408 => RequestClassInterface::STATUS_FAILED,
            409 => RequestClassInterface::STATUS_FAILED,
            500 => RequestClassInterface::STATUS_FAILED,
            default => RequestClassInterface::STATUS_FAILED,
        };
    }


    /**
     * @throws \JsonException
     */
    public static function getBankInfo($searchValue, $searchKey = 'short_name', $returnKey = 'code'): ?array
    {
        $entries = json_decode(file_get_contents(__DIR__ . '/bank.json'), true, 512, JSON_THROW_ON_ERROR);
        $response = self::findBankEntry($entries, $searchKey, $searchValue);
        if (!empty($response)) {
            return [
                'name' => $response['bank_name'],
                'code' => $response['bank_code']
            ];
        }
        return null;
    }

    public static function getOperatorInfo(
        $searchValue,
        $searchKey = 'operator_code',
        $returnKey = 'operator_short_name'
    )
    {
        $entries = json_decode(file_get_contents(__DIR__ . '/operator.json'), true, 512, JSON_THROW_ON_ERROR);
        $response = self::findBankEntry($entries, $searchKey, $searchValue);
        if (!empty($response)) {
            return $response[$returnKey];
        }
        return null;
    }

    public static function findBankEntry($entries, $searchKey, $searchValue)
    {
        foreach ($entries as $entry) {
            if (isset($entry[$searchKey]) && $entry[$searchKey] === $searchValue) {
                return $entry;
            }
        }
        return null; // Return null if no match found
    }
}


