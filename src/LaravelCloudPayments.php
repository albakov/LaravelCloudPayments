<?php

namespace Albakov\LaravelCloudPayments;

use Albakov\LaravelCloudPayments\Exceptions\Validation;

class LaravelCloudPayments
{
    /**
     * System language
     * @param string $cultureName
    */
    public $cultureName;

    /**
     * Public Id from CloudPayments account
     * @param string $publicId
    */
    public $publicId;

    /**
     * API Secret from CloudPayments account
     * @param string $apiSecret
    */
    public $apiSecret;

    /**
     * CloudPayments API URL
     * @param string $apiUrl
    */
    public $apiUrl;

    /**
     * Save properties
    */
    public function __construct()
    {
        $this->cultureName = config('cloudpayments.cultureName');
        $this->publicId = config('cloudpayments.publicId');
        $this->apiSecret = config('cloudpayments.apiSecret');
        $this->apiUrl = config('cloudpayments.apiUrl');
    }

    /**
     * create request to the CloudPayments API
     * @param array $array
     * @return array
    */
    private function request($array)
    {
        $array['data']['CultureName'] = $this->cultureName;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$this->apiUrl}{$array['url']}");
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->publicId}:{$this->apiSecret}");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($array['data']));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        $out = curl_exec($curl);
        curl_close($curl);

        return json_decode($out, true);
    }

    /**
     * Test API
     * @return array
     */
    public function testAPI()
    {
        return $this->request([
            'url' => '/test',
        ]);
    }

    /**
     * Create cards payment request
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function cardsCharge($array)
    {
        $data = $this->validateData(
            $array,
            ['Amount', 'Currency', 'IpAddress', 'Name', 'CardCryptogramPacket']
        );

        return $this->request([
            'url' => '/payments/cards/charge',
            'data' => $data,
        ]);
    }

    /**
     * Create cards 2-Step payment request
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function cardsAuth($array)
    {
        $data = $this->validateData(
            $array,
            ['Amount', 'Currency', 'IpAddress', 'Name', 'CardCryptogramPacket']
        );

        return $this->request([
            'url' => '/payments/cards/auth',
            'data' => $data,
        ]);
    }

    /**
     * Get info for 3-D Secure Transaction
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function cardsPost3ds($array)
    {
        $data = $this->validateData($array, ['TransactionId', 'PaRes']);

        return $this->request([
            'url' => '/payments/cards/post3ds',
            'data' => $data,
        ]);
    }

    /**
     * Create token payment request
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function tokensCharge($array)
    {
        $data = $this->validateData($array, ['Amount', 'Currency', 'AccountId', 'Token']);

        return $this->request([
            'url' => '/payments/tokens/charge',
            'data' => $data,
        ]);
    }

    /**
     * Create token 2-Step payment request
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function tokensAuth($array)
    {
        $data = $this->validateData($array, ['Amount', 'Currency', 'AccountId', 'Token']);

        return $this->request([
            'url' => '/payments/tokens/auth',
            'data' => $data,
        ]);
    }

    /**
     * Confirm payment
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function transactionsConfirm($array)
    {
        $data = $this->validateData($array, ['TransactionId', 'Amount']);
        
        return $this->request([
            'url' => '/payments/confirm',
            'data' => $data,
        ]);
    }

    /**
     * Refund payment
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function transactionsRefund($array)
    {
        $data = $this->validateData($array, ['TransactionId', 'Amount']);
       
        return $this->request([
            'url' => '/payments/refund',
            'data' => $data,
        ]);
    }

    /**
     * Cancel payment
     * @param int $transactionId
     * @return array
     * @throws Validation
     */
    public function transactionsVoid($transactionId)
    {
        if (empty($transactionId)) {
            throw new Validation(['transactionId']);
        }
       
        return $this->request([
            'url' => '/payments/void',
            'data' => ['TransactionId' => (int) $transactionId],
        ]);
    }

    /**
     * Get transaction info by transaction id
     * @param int $transactionId
     * @return array
     * @throws Validation
     */
    public function transactionsGet($transactionId)
    {
        if (empty($transactionId)) {
            throw new Validation(['transactionId']);
        }

        return $this->request([
            'url' => '/payments/get',
            'data' => ['TransactionId' => (int) $transactionId],
        ]);
    }

    /**
     * Get payment info by payment invoice id
     * @param int|string $invoiceId
     * @return array
     * @throws Validation
     */
    public function paymentsFind($invoiceId)
    {
        if (empty($invoiceId)) {
            throw new Validation(['InvoiceId']);
        }

        return $this->request([
            'url' => '/payments/find',
            'data' => ['InvoiceId' => (string) $invoiceId],
        ]);
    }

    /**
     * Get transaction list
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function transactionsList($array)
    {
        $data = $this->validateData($array, ['Date']);

        return $this->request([
            'url' => '/payments/list',
            'data' => $data,
        ]);
    }

    /**
     * Create subscription
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function subscriptionsCreate($array)
    {
        $data = $this->validateData(
            $array,
            [
                'Token',
                'AccountId',
                'Description',
                'Email',
                'Amount',
                'Currency',
                'RequireConfirmation',
                'StartDate',
                'Interval',
                'Period'
            ]
        );

        return $this->request([
            'url' => '/subscriptions/create',
            'data' => $data,
        ]);
    }

    /**
     * Get subscription info
     * @param int $id
     * @return array
     * @throws Validation
     */
    public function subscriptionsInfo($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->request([
            'url' => '/subscriptions/get',
            'data' => ['Id' => (int) $id],
        ]);
    }

    /**
     * Find subscriptions
     * @param string $accountId
     * @return array
     * @throws Validation
     */
    public function subscriptionsFind($accountId)
    {
        if (empty($accountId)) {
            throw new Validation(['accountId']);
        }

        return $this->request([
            'url' => '/subscriptions/find',
            'data' => ['accountId' => (string) $accountId],
        ]);
    }

    /**
     * Update subscription
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function subscriptionsUpdate($array)
    {
        $data = $this->validateData($array, ['Id']);

        return $this->request([
            'url' => '/subscriptions/update',
            'data' => $data,
        ]);
    }

    /**
     * Cancel subscription
     * @param $id
     * @return array
     * @throws Validation
     */
    public function subscriptionsCancel($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->request([
            'url' => '/subscriptions/cancel',
            'data' => ['Id' => (int) $id],
        ]);
    }

    /**
     * Order create
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function ordersCreate($array)
    {
        $data = $this->validateData($array, ['Amount', 'Currency', 'Description']);

        return $this->request([
            'url' => '/orders/create',
            'data' => $data,
        ]);
    }

    /**
     * Cancel order
     * @param string $id
     * @return array
     * @throws Validation
     */
    public function ordersCancel($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->request([
            'url' => '/orders/cancel',
            'data' => ['Id' => (string) $id],
        ]);
    }

    /**
     * Start session Apple Pay
     * @param $validationUrl
     * @return array
     * @throws Validation
     */
    public function applePayStartSession($validationUrl)
    {
        if (empty($validationUrl)) {
            throw new Validation(['ValidationUrl']);
        }

        return $this->request([
            'url' => '/applepay/startsession',
            'data' => ['ValidationUrl' => (string) $validationUrl],
        ]);
    }

    /**
     * Start kkt fiscalize
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function kktFiscalize($array)
    {
        $data = $this->validateData(
            $array,
            [
                'Inn',
                'DeviceNumber',
                'FiscalNumber',
                'RegNumber',
                'Url',
                'Ofd',
                'TaxationSystem'
            ]
        );

        return $this->request([
            'url' => '/kkt/fiscalize',
            'data' => $data,
        ]);
    }

    /**
     * Start kkt fiscalize
     * @param array $array
     * @return array
     * @throws Validation
     */
    public function kktReceipt($array)
    {
        $data = $this->validateData($array, ['Inn', 'Type', 'CustomerReceipt']);

        return $this->request([
            'url' => '/kkt/receipt',
            'data' => $data,
        ]);
    }

    /**
     * Check if array contains required values
     * @param $array
     * @param $rules
     * @return mixed
     * @throws Validation
     */
    private function validateData($array, $rules)
    {
        $arrayDiff = array_diff($rules, array_keys($array));

        if (count($arrayDiff) > 0) {
            throw new Validation($arrayDiff);
        }

        return $array;
    }
}
