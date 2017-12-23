<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),
        'timeout' => false,
        'options' => [
            'collate' => true,
            'disable-external-links' => true,
            'disable-internal-links' => true,
            'enable-smart-shrinking' => true,

            'margin-bottom' => '1cm',
            'margin-left' => '1cm',
            'margin-right' => '1cm',
            'margin-top' => '1cm',
            'page-size' => 'letter',
            'page-width' => '5mm',
            'zoom' => 0.6,
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => base_path('vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64'),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],
];
