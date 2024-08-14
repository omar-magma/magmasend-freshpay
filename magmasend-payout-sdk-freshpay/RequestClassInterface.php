<?php

namespace App\SDK\FreshPay;

interface RequestClassInterface
{
    const STATUS_FAILED = 'FAILED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_DELIVERING = 'SENT-FOR-DELIVERY';
    const STATUS_SUCCESS = 'SUCCESS';
    const MESSAGE_SUCCESS = 'SUCCESS';
    const MESSAGE_FAILED = 'FAILED';
    const MESSAGE_BAD_REQUEST = 'BAD_REQUEST';


    /**
     * RequestClassInterface constructor.
     * This method must instantiate the SDK class with the partner credentials and the system tag
     * @param  string|null  $tag
     * @param  array|null  $credentials
     */
    public function __construct(string $tag = null, array $credentials = null);

    /**
     * This method is called to return our balance on the partner side
     * @param  object|null  $transaction
     * @return array
     */
    public function getBalance($transaction = null): array;

    /**
     * This method is called to send a transaction on the partner side
     * @param  object  $transaction
     * @return array
     */
    public function doTransaction($transaction): array;

    /**
     * This method is called to check the status of a transaction on the partner side
     * @param  object  $transaction
     * @return array
     */
    public function checkTransaction($transaction): array;

    /**
     * This method is called to when a callback is received form the partner side
     * @param  array|null  $postDataArray
     * @return array
     */
    public static function callback(array $postDataArray = null): array;

    /**
     * This method is called to check the account on the partner side
     * @param  object  $transaction
     * @return array|null
     */
    public function checkAccount($transaction): array|null;
}
