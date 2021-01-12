<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$pdfBinary           = null;
$pdfBinaryCandidates = [
    '/app/bin/wkhtmltopdf',
    '/usr/local/bin/wkhtmltopdf',
];
foreach ($pdfBinaryCandidates as $pdfPath) {
    if (file_exists($pdfPath)) {
        $pdfBinary = $pdfPath;
        break;
    }
}
//if ( ! $pdfBinary) {
//    throw new \Exception('wkhtmltopdf binary was not found.', 500);
//}
$imgBinary           = null;
$imgBinaryCandidates = [
    '/app/bin/wkhtmltoimage',
    '/usr/local/bin/wkhtmltoimage',
];
foreach ($imgBinaryCandidates as $imgPath) {
    if (file_exists($imgPath)) {
        $imgBinary = $imgPath;
        break;
    }
}
//if ( ! $imgBinary) {
//    throw new \Exception('wkhtmltoimage binary was not found.', 500);
//}

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
