<?php

namespace Albakov\LaravelCloudPayments;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // php artisan vendor:publish --provider='Albakov\CloudPayments\ServiceProvider' --tag=config
        $this->publishes([
            __DIR__ . '/../config/cloudpayments.php' => config_path('cloudpayments.php'),
        ], 'config');

    }

    public function register()
    {
        // Config
        $this->mergeConfigFrom(__DIR__ . '/../config/cloudpayments.php', 'cloudpayments');

        $this->app->bind('laravelcloudpayments', function () {
            return new LaravelCloudPayments;
        });
    }
}
