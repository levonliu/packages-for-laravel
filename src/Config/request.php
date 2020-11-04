<?php
return [
    'base_uri'     => env('REQUEST_BASE_URI'),
    'curl_timeout' => env('REQUEST_CURL_TIMEOUT', 240),
    'verify'       => env('REQUEST_VERIFY', FALSE)
];
