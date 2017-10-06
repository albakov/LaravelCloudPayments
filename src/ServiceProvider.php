<?php

namespace Albakov\CloudPayments;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        // Route
        // Insert: Albakov\CloudPayments\ServiceProvider::class 
        // in app.php before:
        // App\Providers\RouteServiceProvider::class
        include __DIR__ . '/routes/notifier.php';
        
        // You can do:
        // php artisan vendor:publish --provider='Albakov\CloudPayments\ServiceProvider' --tag=config
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