<?php

namespace Albakov\LaravelCloudPayments;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return LaravelCloudPayments::class;
    }
}
