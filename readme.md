# CloudPayments for Laravel 5
Это расширение для Laravel 5 позволит удобно работать с CloudPayments API.

### Установка

Используя Composer:

```
composer require albakov/laravelcloudpayments
```

В массив providers в файле app/config/app.php добавить:

```
Albakov\LaravelCloudPayments\ServiceProvider::class,
```

В массив aliases:

```
'CloudPayments' => Albakov\LaravelCloudPayments\Facade::class,
```

### Настройка

Необходимо опубликовать конфигурационный файл командой:

```
php artisan vendor:publish --provider='Albakov\LaravelCloudPayments\ServiceProvider' --tag=config
```

В папке config появится файл cloudpayments.php. Необходимо указать свои данные:

```
return [
    'apiSecret' => 'YOUR_API_SECRET',
    'publicId' => 'YOUR_PUBLICID',
    'apiUrl' => 'https://api.cloudpayments.ru',
    'cultureName' => 'en-US', // For more languages: https://cloudpayments.ru/Docs/Api#language
];
```

### Примеры использования

#### Оплата по криптограмме (для одностадийного платежа).
Подробнее о методе здесь: https://cloudpayments.ru/Docs/Api#payWithCrypto

```
<?php

namespace App\Http\Controllers;

use CloudPayments;

~ ~ ~

public function doPayment()
{
    $array = [
        'Amount' => $order['amount'], // Required
        'Currency' => 'USD', // Required
        'Name' => $order['firstname'], // Required
        'IpAddress' => getHostByName(getHostName()), // Required
        'CardCryptogramPacket' => $CardCryptogramPacket, // Required
        'InvoiceId' => $order['orderId'],
        'Description' => 'Payment for order №' . $order['orderId'],
        'AccountId' => '999',
        'Email' => $order['email'],
        'JsonData' => json_encode([
            'middleName' => $order['lastname'],
            'lastName' => $order['surname'],
            'phone' => $order['phone'],
        ]),
    ];

    // Trying to do Payment
    try {
        $result = CloudPayments::cardsCharge($array);
    } catch (\Exception $e) {
        $result = $e->getMessage();
    }
}

~ ~ ~
```

#### Выгрузка списка транзакций
Подробнее о методе здесь: https://cloudpayments.ru/Docs/Api#payWithCrypto

```
~ ~ ~
    
$array = [
    'Date' => '2017-10-06', // Required
    'TimeZone' => 'MSK', // Timezones: https://cloudpayments.ru/Docs/Directory#time-zones
];

// Trying to do request
try {
    $result = CloudPayments::transactionsList($array);
} catch (\Exception $e) {
    $result = $e->getMessage();
}

~ ~ ~
```

### Уведомления
Подробнее здесь: https://cloudpayments.ru/Docs/Notifications

Используются для проверки возможности выполнить оплату, для информирования об успешных и неуспешных платежах, для оповещения об изменении подписок на рекуррентные платежи, а также для информирования о выданных кассовых чеках.

Для обработки уведомлений вы можете использовать трейт (trait) [LaravelCloudPayments/src/Notifications.php](https://github.com/albakov/LaravelCloudPayments/blob/master/src/Notifications.php)

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;

class MyNotifier {
~ ~ ~

    // Use trait
    use \Albakov\LaravelCloudPayments\Notifications;

    /**
     * Check payment
     * https://cloudpayments.ru/Docs/Notifications#check
     * @param Illuminate\Http\Request $request
     * @return json
     */
    public function check(Request $request)
    {
        $data = $this->validateAll(Request $request);
        return response()->json($data);
    }
    
    /**
     * Confirm payment
     * https://cloudpayments.ru/Docs/Notifications#pay
     * @param Illuminate\Http\Request $request
     * @return json
     */
    public function pay(Request $request)
    {
        $data = $this->validateAll(Request$request);
         
        if ((int) $data['code'] === 0) {
            // payment success
            // mark order payment status - success
            // send email to admin and customer
            // etc ...
        }
        
        return response()->json($data);
    }
    
    /**
     * Check Secret, orderId, Amount
     * @param Illuminate\Http\Request $request
     * @return json
     */
    public function validateAll($request)
    {
        // Check secrets
        $result = $this->validateSecrets($request);

        if ($result['code'] !== 0) {
            return $result;
        }
        
        // Get order
        $order = Order::find($request->InvoiceId);
        
        // Check orders
        $result = $this->validateOrder($request->InvoiceId, $order->id);

        if ($result['code'] !== 0) {
            return $result;
        }
        
        // Check amounts
        $result = $this->validateAmount($request->Amount, $order->amount);

        if ($result['code'] !== 0) {
            return $result;
        }

        return ['code' => 0]; // Success
    }

~ ~ ~

}
```

Не забудьте указать маршруты:

```
<?php

~ ~ ~

Route::group(['prefix' => 'cloudpayments'], function() {
    Route::match(['GET', 'POST'], 'check', 'MyNotifier@check');
    Route::match(['GET', 'POST'], 'pay', 'MyNotifier@pay');
});

~ ~ ~
```

Для получения уведомлений (check, pay, fail, confirm, ...) необходимо исключить URL от CSRF валидации в файле app/Http/MiddlewareVerifyCsrfToken:

```
protected $except = [
    // ...
    'cloudpayments/*'
];
```
