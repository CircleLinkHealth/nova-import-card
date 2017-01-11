<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 8:00 PM
 */

return [

    /*
    |--------------------------------------------------------------------------
    | CCD Importer Strategies Map
    |--------------------------------------------------------------------------
    |
    | This is used as a layer between ImporterRoutines in the database and the
    | application. The ImporterRoutines' ID is it's key number in strategiesMap
    | array.
    | Example: App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNamesList::class
    |       has an ID of 0.
    */

    'parsing' => [

    ],

    'storage' => [

    ],

    'validators' => [
        \App\Importer\Section\Validators\ValidStatus::class,
    ],

];