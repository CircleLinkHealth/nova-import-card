<?php

/**
 *
 * ATTENTION: NEVER CHANGE THE ORDER OF THE ELEMENTS IN THE CONFIG ARRAYS IN THIS FILE!
 *
 * They are used as a map between the db and the actual classes used (or any other data)
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
    | Example: App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNameAllergiesListParser::class
    |       has an ID of 0.
    */

    'parsing' => [
        App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNameAllergiesListParser::class,
        App\CLH\CCD\Importer\ParsingStrategies\Problems\NameCodeAndCodeSysNameProblemsListParser::class,
        App\CLH\CCD\Importer\ParsingStrategies\Problems\ProblemsToMonitorParser::class,
        App\CLH\CCD\Importer\ParsingStrategies\Medications\ProductNameAndTextMedsListParser::class,
        App\CLH\CCD\Importer\ParsingStrategies\Medications\ReferenceTitleAndSigMedsListParser::class,
    ],

    'storage' => [
        App\CLH\CCD\Importer\StorageStrategies\Allergies\AllergiesListStorageStrategy::class,
        App\CLH\CCD\Importer\StorageStrategies\Medications\MedicationsListStorageStrategy::class,
        App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsListStorageStrategy::class,
        App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitorStorageStrategy::class,
    ],

    'validation' => [
        App\CLH\CCD\Importer\ValidationStrategies\ImportAllItems::class,
        App\CLH\CCD\Importer\ValidationStrategies\ValidEndDate::class,
        App\CLH\CCD\Importer\ValidationStrategies\ValidStartDateNoEndDate::class,
        App\CLH\CCD\Importer\ValidationStrategies\ValidStatus::class,
    ],

];
