<?php

namespace App\CLH\CCD\CarePlanGenerator;


use App\CLH\CCD\CarePlanGenerator\Importers\Allergies\AllergiesListImporter;
use App\CLH\CCD\CarePlanGenerator\Importers\Medications\MedicationsListImporter;
use App\CLH\CCD\CarePlanGenerator\Importers\Problems\ProblemsListImporter;
use App\CLH\CCD\CarePlanGenerator\Importers\Problems\ProblemsToMonitorImporter;
use App\CLH\CCD\CarePlanGenerator\Parsers\Allergies\AllergenNameAllergiesListParser;
use App\CLH\CCD\CarePlanGenerator\Parsers\Problems\NameCodeAndCodeSysNameProblemsListParser;
use App\CLH\CCD\CarePlanGenerator\Parsers\Problems\ProblemsToMonitorParser;
use App\CLH\CCD\CarePlanGenerator\Parsers\Medications\ProductNameAndTextMedsListParser;
use App\CLH\CCD\CarePlanGenerator\Parsers\Medications\ReferenceTitleAndSigMedsListParser;
use App\CLH\CCD\CarePlanGenerator\Validators\ImportAllItems;
use App\CLH\CCD\CarePlanGenerator\Validators\ValidEndDate;
use App\CLH\CCD\CarePlanGenerator\Validators\ValidStartDateNoEndDate;
use App\CLH\CCD\CarePlanGenerator\Validators\ValidStatus;

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
        AllergiesListImporter::class => 'AllergiesListImporter',
        //Medications
        MedicationsListImporter::class => 'MedicationsListImporter',
        //Problems
        ProblemsListImporter::class => 'ProblemsListImporter',
        ProblemsToMonitorImporter::class => 'ProblemsToMonitorImporter'
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