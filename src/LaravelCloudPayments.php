<?php

namespace Albakov\LaravelCloudPayments;

use Albakov\LaravelCloudPayments\Exceptions\Validation;

class LaravelCloudPayments
{

    /**
     * System language
     * @param string $CultureName
    */
    public $CultureName;

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
        $this->CultureName = config('cloudpayments.CultureName');
        $this->publicId = config('cloudpayments.publicId');
        $this->apiSecret = config('cloudpayments.apiSecret');
        $this->apiUrl = config('cloudpayments.apiUrl');
    }

    /**
     * create request to the CloudPayments API
     * @param array $array
     * @return array
    */
    private function _request($array)
    {
        $array['data']['CultureName'] = $this->CultureName;

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
     * @return json
    */
    public function testAPI()
    {
        return $this->_request([
            'url' => '/test',
        ]);
    }

    /**
     * Create cards payment request
     * @param array $array
     * @return json
     * @return Exception
    */
    public function cardsCharge($array)
    {
        $data = $this->_validateData($array, ['Amount', 'Currency', 'IpAddress', 'Name', 'CardCryptogramPacket']);

        return $this->_request([
            'url' => '/payments/cards/charge',
            'data' => $data,
        ]);
    }

    /**
     * Create cards 2-Step payment request
     * @param array $array
     * @return json
     * @return Exception
    */
    public function cardsAuth($array)
    {
        $data = $this->_validateData($array, ['Amount', 'Currency', 'IpAddress', 'Name', 'CardCryptogramPacket']);

        return $this->_request([
            'url' => '/payments/cards/auth',
            'data' => $data,
        ]);
    }

    /**
     * Get info for 3-D Secure Transaction
     * @param array $array
     * @return json
     * @return Exception
    */
    public function cardsPost3ds($array)
    {
        $data = $this->_validateData($array, ['TransactionId', 'PaRes']);

        return $this->_request([
            'url' => '/payments/cards/post3ds',
            'data' => $data,
        ]);
    }

    /**
     * Create token payment request
     * @param array $array
     * @return json
     * @return Exception
    */
    public function tokensCharge($array)
    {
        $data = $this->_validateData($array, ['Amount', 'Currency', 'AccountId', 'Token']);

        return $this->_request([
            'url' => '/payments/tokens/charge',
            'data' => $data,
        ]);
    }

    /**
     * Create token 2-Step payment request
     * @param array $array
     * @return json
     * @return Exception
    */
    public function tokensAuth($array)
    {
        $data = $this->_validateData($array, ['Amount', 'Currency', 'AccountId', 'Token']);

        return $this->_request([
            'url' => '/payments/tokens/auth',
            'data' => $data,
        ]);
    }

    /**
     * Confirm payment
     * @param array $array
     * @return json
     * @return Exception
    */
    public function transactionsConfirm($array)
    {
        $data = $this->_validateData($array, ['TransactionId', 'Amount']);
        
        return $this->_request([
            'url' => '/payments/confirm',
            'data' => $data,
        ]);
    }

    /**
     * Refund payment
     * @param array $array
     * @return json
     * @return Exception
    */
    public function transactionsRefund($array)
    {
        $data = $this->_validateData($array, ['TransactionId', 'Amount']);
       
        return $this->_request([
            'url' => '/payments/refund',
            'data' => $data,
        ]);
    }

    /**
     * Cancel payment
     * @param int $transactionId
     * @return json
     * @return Exception
    */
    public function transactionsVoid($transactionId)
    {
        if (empty($transactionId)) {
            throw new Validation(['transactionId']);
        }
       
        return $this->_request([
            'url' => '/payments/void',
            'data' => ['TransactionId' => (int) $transactionId],
        ]);
    }

    /**
     * Get transaction info by transaction id
     * @param int $transactionId
     * @return json
     * @return Exception
    */
    public function transactionsGet($transactionId)
    {
        if (empty($transactionId)) {
            throw new Validation(['transactionId']);
        }

        return $this->_request([
            'url' => '/payments/get',
            'data' => ['TransactionId' => (int) $transactionId],
        ]);
    }

    /**
     * Get payment info by payment invoice id
     * @param int|string $invoiceId
     * @return json
     * @return Exception
    */
    public function paymentsFind($invoiceId)
    {
        if (empty($invoiceId)) {
            throw new Validation(['InvoiceId']);
        }

        return $this->_request([
            'url' => '/payments/find',
            'data' => ['InvoiceId' => (string) $invoiceId],
        ]);
    }

    /**
     * Get transaction list
     * @param array $array
     * @return json
     * @return Exception
    */
    public function transactionsList($array)
    {
        $data = $this->_validateData($array, ['Date']);

        return $this->_request([
            'url' => '/payments/list',
            'data' => $data,
        ]);
    }

    /**
     * Create subscription
     * @param array $array
     * @return json
     * @return Exception
    */
    public function subscriptionsCreate($array)
    {
        $data = $this->_validateData(
            $array,
            ['Token','AccountId','Description','Email', 'Amount',
            'Currency','RequireConfirmation', 'StartDate','Interval','Period']
        );

        return $this->_request([
            'url' => '/subscriptions/create',
            'data' => $data,
        ]);
    }

    /**
     * Get subscription info
     * @param int $id
     * @return json
     * @return Exception
    */
    public function subscriptionsInfo($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->_request([
            'url' => '/subscriptions/get',
            'data' => ['Id' => (int) $id],
        ]);
    }

    /**
     * Find subscriptions
     * @param string $accountId
     * @return json
     * @return Exception
    */
    public function subscriptionsFind($accountId)
    {
        if (empty($accountId)) {
            throw new Validation(['accountId']);
        }

        return $this->_request([
            'url' => '/subscriptions/find',
            'data' => ['accountId' => (string) $accountId],
        ]);
    }

    /**
     * Update subscription
     * @param array $array
     * @return json
     * @return Exception
    */
    public function subscriptionsUpdate($array)
    {
        $data = $this->_validateData($array, ['Id']);

        return $this->_request([
            'url' => '/subscriptions/update',
            'data' => $data,
        ]);
    }

    /**
     * Cancel subscription
     * @param int $Id
     * @return json
     * @return Exception
    */
    public function subscriptionsCancel($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->_request([
            'url' => '/subscriptions/cancel',
            'data' => ['Id' => (int) $id],
        ]);
    }

    /**
     * Order create
     * @param array $array
     * @return json
     * @return Exception
    */
    public function ordersCreate($array)
    {
        $data = $this->_validateData($array, ['Amount', 'Currency', 'Description']);

        return $this->_request([
            'url' => '/orders/create',
            'data' => $data,
        ]);
    }

    /**
     * Cancel order
     * @param string $id
     * @return json
     * @return Exception
    */
    public function ordersCancel($id)
    {
        if (empty($id)) {
            throw new Validation(['Id']);
        }

        return $this->_request([
            'url' => '/orders/cancel',
            'data' => ['Id' => (string) $id],
        ]);
    }

    /**
     * Start session Apple Pay
     * @param string $ValidationUrl
     * @return json
     * @return Exception
    */
    public function applePayStartSession($validationUrl)
    {
        if (empty($validationUrl)) {
            throw new Validation(['ValidationUrl']);
        }

        return $this->_request([
            'url' => '/applepay/startsession',
            'data' => ['ValidationUrl' => (string) $validationUrl],
        ]);
    }

    /**
     * Start kkt fiscalize
     * @param array $array
     * @return json
     * @return Exception
    */
    public function kktFiscalize($array)
    {
        $data = $this->_validateData($array, ['Inn', 'DeviceNumber', 'FiscalNumber', 'RegNumber', 'Url', 'Ofd', 'TaxationSystem']);

        return $this->_request([
            'url' => '/kkt/fiscalize',
            'data' => $data,
        ]);
    }

    /**
     * Start kkt fiscalize
     * @param array $array
     * @return json
     * @return Exception
    */
    public function kktReceipt($array)
    {
        $data = $this->_validateData($array, ['Inn', 'Type', 'CustomerReceipt']);

        return $this->_request([
            'url' => '/kkt/receipt',
            'data' => $data,
        ]);
    }

   /**
    * Check if array contains required values
   */
    private function _validateData($array, $rules)
    {
        $arrayDeff = array_diff($rules, array_keys($array));

        if (count($arrayDeff)) {
            throw new Validation($arrayDeff);
        }

        return $array;
    }
}
