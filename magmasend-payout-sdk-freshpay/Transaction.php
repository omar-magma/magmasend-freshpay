<?php

namespace App\SDK\FreshPay;

// TODO: This is all the attributes for transaction that you can use
final class Transaction
{
    public string $country; // Receiving Country Code, example CI, SN ...
    public string $phone; // Receiving Phone number in local format
    public string $amount; // Receiving Amount
    public string $currency; // Receiving Currency, example XOF, XAF ...

    public string $magma_id; // Magma transaction ID, this is our transaction ID that we will use to track the transaction
    public string|null $partner_id; // Partner transaction ID, this is the transaction ID that the partner will use to track the transaction

    public string $status; // Transaction status, example SENT-FOR-DELIVERY, SUCCESS, FAILED

    public string $payment_method; // Receiving Payment method,
    public string $delivery_method; // Receiving Payment method,
    public string|null $sender_firstname;
    public string|null $sender_lastname;
    public string|null $sender_address;
    public string|null $sender_city;
    public string|null $sender_country;
    public string|null $sender_currency;
    public string|null $receiver_firstname;
    public string|null $receiver_lastname;
    public string|null $receiver_address;
    public string|null $receiver_city;

    public string|null $receiver_bank_name;
    public string|null $receiver_bank_address;
    public string|null $receiver_bank_account_number;
    public string|null $receiver_bank_sort_code;
    public string|null $receiver_bank_swift_code;
    public string|null $receiver_company_name;

    //all fields are not required
    public function __constructor(
        string      $country,
        string      $phone,
        string      $amount,
        string      $currency,
        string      $magma_id,
        string|null $partner_id,
        string      $status,
        string      $payment_method,
        string|null $sender_firstname,
        string|null $sender_lastname,
        string|null $sender_address,
        string|null $sender_city,
        string|null $sender_country,
        string|null $sender_currency,
        string|null $receiver_firstname,
        string|null $receiver_lastname,
        string|null $receiver_address,
        string|null $receiver_city,
        string|null $receiver_bank_name,
        string|null $receiver_bank_address,
        string|null $receiver_bank_account_number,
        string|null $receiver_bank_sort_code,
        string|null $receiver_bank_swift_code,
        string|null $receiver_company_name
    )
    {
    }

}
