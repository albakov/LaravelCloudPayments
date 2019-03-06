<?php

namespace Albakov\LaravelCloudPayments\Exceptions;

class Validation extends \Exception
{
    /**
     * Custom Exception
     * @param array $array
     */
    public function __construct($array)
    {
        $error = 'Required params: ' . implode(',', $array);
        parent::__construct($error);
    }
}
