<?php

/*
|--------------------------------------------------------------------------
| CloudPayments Notifier
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'cloudpayments'], function() {
    Route::match(['GET', 'POST'], 'check', 'Albakov\CloudPayments\Http\Notifier@check');
    Route::match(['GET', 'POST'], 'pay', 'Albakov\CloudPayments\Http\Notifier@pay');
});