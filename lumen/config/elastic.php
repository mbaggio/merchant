<?php

return [
    'elastic_url' => env('ELASTICSEARCH_HOST', ''),
    'elastic_port' => env('ELASTICSEARCH_PORT', ''),
    'elastic_timezone' => env('ELASTICSEARCH_TZ', ''),
    'elastic_user' => env('ELASTICSEARCH_USER', ''),
    'elastic_pass' => env('ELASTICSEARCH_PASS', '')
];