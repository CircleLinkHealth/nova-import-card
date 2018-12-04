<?php

//PDF Binary
$debianPdfLib = '/usr/local/bin/wkhtmltopdf';
$localPdfLib  = base_path('bin/wkhtmltopdf/wkhtmltopdf-amd64');

if (file_exists($debianPdfLib)) {
    $pdfBinary = $debianPdfLib;
} elseif (file_exists($localPdfLib)) {
    $pdfBinary = $localPdfLib;
} else {
    throw new \Exception('wkhtmltopdf binary not found.', 500);
}

//Img Binary
$debianImgLib   = '/usr/local/bin/wkhtmltoimage';
$composerImgLib = base_path('bin/wkhtmltoimage/wkhtmltoimage-amd64');

if (file_exists($debianImgLib)) {
    $imgBinary = $debianImgLib;
} elseif (file_exists($composerImgLib)) {
    $imgBinary = $composerImgLib;
} else {
    throw new \Exception('wkhtmltoimage binary not found.', 500);
}

return [
    'pdf'   => [
        'enabled' => true,
        'binary'  => $pdfBinary,
        'timeout' => false,
        'options' => [
            'collate'                => true,
            'disable-external-links' => true,
            'disable-internal-links' => true,
            'enable-smart-shrinking' => true,

            'margin-bottom' => '1cm',
            'margin-left'   => '1cm',
            'margin-right'  => '1cm',
            'margin-top'    => '1cm',
            'page-size'     => 'letter',
            'page-width'    => '5mm',
            'zoom'          => 0.8,
        ],
        'env'     => [],
    ],
    'image' => [
        'enabled' => true,
        'binary'  => $imgBinary,
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
];
