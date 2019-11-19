<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$localBinPath = '/usr/local/bin/wkhtmltopdf';
$pdfBinary    = null;
if (file_exists($localBinPath)) {
    $pdfBinary = $localBinPath;
}
//    throw new \Exception('wkhtmltopdf binary was not found.', 500);

//Img Binary
$debianImgLib = '/usr/local/bin/wkhtmltoimage';
$imgBinary    = null;
if (file_exists($debianImgLib)) {
    $imgBinary = $debianImgLib;
}
//    throw new \Exception('wkhtmltoimage binary not found.', 500);

return [
    'pdf' => [
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
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary'  => $imgBinary,
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
];
