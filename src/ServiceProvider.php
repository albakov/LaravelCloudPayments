<?php

namespace Albakov\CloudPayments;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        // Route
        include __DIR__ . '/routes/notifier.php';
        
        $this->publishes([
            __DIR__ . '/../config/cloudpayments.php' => config_path('cloudpayments.php'),
        ], 'config');

    }

    public function register()
    {
        // Config
        $this->mergeConfigFrom(__DIR__ . '/../config/cloudpayments.php', 'cloudpayments');
 
        $this->app->bind('cloudpayments', function() {
            return new CloudPayments;
        });
    }

}