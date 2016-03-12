<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfigStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMetaStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMetaParser;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\User;

class ImportManager
{
    private $allergiesImport;
    private $demographicsImport;
    private $medicationsImport;
    private $problemsImport;
    private $ccdaStrategies;
    private $user;

    public function __construct(array $allergiesImport = null,
                                DemographicsImport $demographicsImport,
                                array $medicationsImport,
                                array $problemsImport,
                                array $strategies,
                                User $user)
    {
        $this->allergiesImport = $allergiesImport;
        $this->demographicsImport = $demographicsImport;
        $this->medicationsImport = $medicationsImport;
        $this->problemsImport = $problemsImport;
        $this->ccdaStrategies = $strategies;
        $this->user = $user;
    }

    public function import()
    {
        $strategies = \Config::get( 'ccdimporterstrategiesmaps' );

        /**
         * Allergies List
         */
        $this->storeAllergies( $strategies[ 'storage' ][ 0 ] );


        /**
         * Problems List
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeProblemsList( $strategies[ 'storage' ][ 2 ] );


        /**
         * Problems To Monitor
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeProblemsToMonitor( $strategies[ 'storage' ][ 3 ] );


        /**
         * Medications List
         */
        //this gets the storage strategy from config/ccdimportersections by sectionId
        $this->storeMedications( $strategies[ 'storage' ][ 1 ] );


        /**
         * The following Sections are the same for each CCD
         */

        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser( new UserMetaTemplate() );
        ( new UserMetaStorageStrategy( $this->user->program_id, $this->user ) )->import( $this->output[ 'userMeta' ] );

        /**
         * Import User Config
         */
        $providerId = !empty($this->output[ 'provider' ]) ? $this->output[ 'provider' ][ 0 ][ 'ID' ] : false;

        /**
         * If a Provider was found, add it to UserConfig before persisting UserConfig
         */
        if ( !$providerId ) {
            $userConf = $this->output[ 'userConfig' ];
        }
        else {
            $userConf = $this->output[ 'userConfig' ];
            $userConf[ 'care_team' ][] = $providerId;
            $userConf[ 'lead_contact' ] = $providerId;
            $userConf[ 'billing_provider' ] = $providerId;
        }

        /**
         * Import Location
         */
        $locationId = !empty($this->output[ 'location' ]) ? $this->output[ 'location' ][ 0 ][ 'id' ] : false;

        /**
         * If location was found, add it to UserConfig
         */
        if ( $locationId ) $userConf[ 'preferred_contact_location' ] = $locationId;

        /**
         * Persist UserConfig
         */
        $userConfigParser = new UserConfigParser( new UserConfigTemplate(), $this->user->program_id );
        ( new UserConfigStorageStrategy( $this->user->program_id, $this->user->ID ) )->import( $userConf );

        /**
         * CarePlan Defaults
         */
        $transitionalCare = new TransitionalCare( $this->user->program_id, $this->user->ID );
        $transitionalCare->setDefaults();

        return true;
    }

    private function storeAllergies($allergiesListStorage)
    {
        if (empty($this->allergiesImport)) return false;

        if ( class_exists( $allergiesListStorage ) ) {
            $storage = new $allergiesListStorage( $this->user->program_id, $this->user );

            $allergiesList = '';

            foreach ( $this->allergiesImport as $allergy ) {
                if ( !isset($allergy->allergen_name) ) continue;
                $allergiesList .= "\n\n";
                $allergiesList .= ucfirst( strtolower( $allergy->allergen_name ) ) . ";";
            }

            $storage->import( $allergiesList );
        }
    }

    private function storeProblemsList($problemsListStorage)
    {
        if (empty($this->problemsImport)) return false;

        if ( class_exists( $problemsListStorage ) ) {
            $storage = new $problemsListStorage( $this->user->program_id, $this->user );

            $problemsList = '';

            foreach ( $this->problemsImport as $problem ) {
                $problemsList .= "\n\n";

                //quick fix to display snomed ct in middletown
                $codeSystemName = function ($problem) {
                    return empty($problem->code_system_name)
                        ? empty($problem->code_system)
                            ?: ($problem->code_system == '2.16.840.1.113883.6.96')
                                ? 'SNOMED CT'
                                : ($problem->code_system == '2.16.840.1.113883.6.4')
                                    ? 'ICD-9'
                                    : ''
                        : $problem->code_system_name;
                };

                $problemsList .= ucwords( strtolower( $problem->name ) ) . ', '
                    . strtoupper( $codeSystemName( $problem ) ) . ', '
                    . $problem->code . ";";
            }


            $storage->import( $problemsList );
        }
    }

    private function storeProblemsToMonitor($problemsToMonitorStorage)
    {
        if (empty($this->problemsImport)) return false;

        if ( class_exists( $problemsToMonitorStorage ) ) {
            $storage = new $problemsToMonitorStorage( $this->user->program_id, $this->user );

            foreach ( $this->problemsImport as $problem ) {
                if ( empty($problem->cpm_problem_id) ) continue;

                $problemsToActivate[] = CPMProblem::find( $problem->cpm_problem_id )->care_item_name;
            }

            $storage->import( $problemsToActivate );
        }
    }

    private function storeMedications($medicationsListStorage)
    {
        if (empty($this->medicationsImport)) return false;

        if ( class_exists( $medicationsListStorage ) ) {
            $storage = new $medicationsListStorage( $this->user->program_id, $this->user );

            $medicationsList = '';

            foreach ( $this->medicationsImport as $medication ) {
                $medicationsList .= "\n\n";
                empty($medication->name)
                    ?: $medicationsList .= ucfirst( strtolower( $medication->name ) );

                $medicationsList .= ucfirst(
                    strtolower(
                        empty($medText = $medication->sig)
                            ? ';'
                            : ', ' . $medText . ";"
                    )
                );

            }


            $storage->import( $medicationsList );
        }
    }
}