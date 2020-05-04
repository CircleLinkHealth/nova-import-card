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
    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timout:
    |
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */

    'pdf' => [
        'enabled' => true,
        'binary'  => $pdfBinary,
        'timeout' => false,
        'options' => [],
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
