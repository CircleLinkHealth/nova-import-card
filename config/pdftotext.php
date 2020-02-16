<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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
    'path' => env('PDF_TO_TEXT_PATH', '/usr/bin/pdftotext'),
];