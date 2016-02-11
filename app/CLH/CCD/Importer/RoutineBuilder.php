<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\StorageStrategies\Allergies\AllergiesListStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Medications\MedicationsListStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsListStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitorStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Allergies\AllergenNameAllergiesListParser;
use App\CLH\CCD\Importer\ParsingStrategies\Problems\NameCodeAndCodeSysNameProblemsListParser;
use App\CLH\CCD\Importer\ParsingStrategies\Problems\ProblemsToMonitorParser;
use App\CLH\CCD\Importer\ParsingStrategies\Medications\ProductNameAndTextMedsListParser;
use App\CLH\CCD\Importer\ParsingStrategies\Medications\ReferenceTitleAndSigMedsListParser;
use App\CLH\CCD\Importer\ValidationStrategies\ImportAllItems;
use App\CLH\CCD\Importer\ValidationStrategies\ValidEndDate;
use App\CLH\CCD\Importer\ValidationStrategies\ValidStartDateNoEndDate;
use App\CLH\CCD\Importer\ValidationStrategies\ValidStatus;

class RoutineBuilder
{
    private $allergiesList;
    private $medicationsList;
    private $problemsList;
    private $problemsToMonitor;

    private $validatorsLookup = [
        ImportAllItems::class => 'ImportAllItems',
        ValidEndDate::class => 'ValidEndDate',
        ValidStartDateNoEndDate::class => 'ValidStartDateNoEndDate',
        ValidStatus::class => 'ValidStatus'
    ];

    private $parsersLookup = [
        //Allergies
        AllergenNameAllergiesListParser::class => 'AllergenNameAllergiesListParser',
        //Medications
        ProductNameAndTextMedsListParser::class => 'ProductNameAndTextMedsListParser',
        ReferenceTitleAndSigMedsListParser::class => 'ReferenceTitleAndSigMedsListParser',
        //Problems
        NameCodeAndCodeSysNameProblemsListParser::class => 'NameCodeAndCodeSysNameProblemsListParser',
        ProblemsToMonitorParser::class => 'ProblemsToMonitorParser'
    ];

    private $importersLookup = [
        //Allergies
        AllergiesListStorageStrategy::class => 'AllergiesListStorageStrategy',
        //Medications
        MedicationsListStorageStrategy::class => 'MedicationsListStorageStrategy',
        //Problems
        ProblemsListStorageStrategy::class => 'ProblemsListStorageStrategy',
        ProblemsToMonitorStorageStrategy::class => 'ProblemsToMonitorStorageStrategy'
    ];

    public function __construct()
    {

    }


    public function getDefaultSettings()
    {
        $default = ImporterSettings::whereEhrName( 'Default Routine' )->first();

        return [
            'allergiesList' => $this->createSectionRuleset( $default->allergiesListValidator, $default->allergiesListParser, $default->allergiesListImporter ),
            'medicationsList' => $this->createSectionRuleset( $default->medicationsListValidator, $default->medicationsListParser, $default->medicationsListImporter ),
            'problemsList' => $this->createSectionRuleset( $default->problemsListValidator, $default->problemsListParser, $default->problemsListImporter ),
            'problemsToMonitor' => $this->createSectionRuleset( $default->problemsToMonitorValidator, $default->problemsToMonitorParser, $default->problemsToMonitorImporter ),
        ];

    }

    private function createSectionRuleset($validator, $parser, $importer)
    {
        return [
                'validator' => array_search( $validator, $this->validatorsLookup ),
                'parser' => array_search( $parser, $this->parsersLookup ),
                'importer' => array_search( $importer, $this->importersLookup ),
        ];
    }
}