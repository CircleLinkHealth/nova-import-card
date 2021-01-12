<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$pdfToTextBinary           = null;
$pdfToTextBinaryCandidates = [
    '/app/bin/pdftotext',
    '/usr/local/bin/pdftotext',
    '/user/bin/pdftotext',
];

foreach ($pdfToTextBinaryCandidates as $pdfPath) {
    if (file_exists($pdfPath)) {
        $pdfToTextBinary = $pdfPath;
        break;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | pdftotext path
    |--------------------------------------------------------------------------
    |
    | This is used by Spatie\pdf-to-text
    | Currently using this reading PDF careplans from UPG for G0506 flow.
    |
    */
    'path' => $pdfToTextBinary,
];
