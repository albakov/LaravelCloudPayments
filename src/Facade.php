<?php

namespace Albakov\CloudPayments;

class Facade extends \Illuminate\Support\Facades\Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return CloudPayments::class;
    }

}