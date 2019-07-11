<?php

return [
    'apiSecret' => env( CLOUDPAYMENT_SECRET, '' ),
    'publicId' => env( CLOUDPAYMENT_PUBLIC_ID, '' ),
    'apiUrl' => env( CLOUDPAYMENT_API_URL, 'https://api.cloudpayments.ru' ),
    'cultureName' => env( CLOUDPAYMENT_CULTURE, 'en-US' ), // https://cloudpayments.ru/Docs/Api#language
];
