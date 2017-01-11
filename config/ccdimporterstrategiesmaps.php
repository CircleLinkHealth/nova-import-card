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
    | Example: App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNamesList::class
    |       has an ID of 0.
    */

    'parsing' => [
        App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNamesList::class,
        App\CLH\CCD\Importer\ParsingStrategies\Problems\NameCodeCodeSysNameList::class,
        App\CLH\CCD\Importer\ParsingStrategies\Problems\ToMonitor::class,
        App\CLH\CCD\Importer\ParsingStrategies\Medications\ProductNameAndTextList::class,
        App\CLH\CCD\Importer\ParsingStrategies\Medications\ReferenceTitleAndSig::class,
    ],

    'storage' => [
        App\CLH\CCD\Importer\StorageStrategies\Allergies\AllergiesList::class,
        App\CLH\CCD\Importer\StorageStrategies\Medications\MedicationsList::class,
        App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsList::class,
        App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor::class,
    ],

    'validation' => [
        App\CLH\CCD\Importer\ValidationStrategies\ImportAllItems::class,
        App\CLH\CCD\Importer\ValidationStrategies\ValidEndDate::class,
        App\CLH\CCD\Importer\ValidationStrategies\ValidStartDateNoEndDate::class,
        \App\Importer\Section\Validators\ValidStatus::class,
        App\CLH\CCD\Importer\ValidationStrategies\Compound\Nestor::class,
    ],

];
