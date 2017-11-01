<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 8:00 PM
 */

return [

    'validators' => [
        \App\Importer\Section\Validators\ValidStatus::class,
        \App\Importer\Section\Validators\ValidEndDate::class,
        \App\Importer\Section\Validators\ValidStartDateNoEndDate::class,
        \App\Importer\Section\Validators\ImportAllItems::class,
    ],

];
