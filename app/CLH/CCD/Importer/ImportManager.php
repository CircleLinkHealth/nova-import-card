<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfigStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMetaStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMetaParser;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\WpUser as User;

class ImportManager
{
    private $output;
    private $user;

    public function __construct($output, User $user)
    {
        $this->output = $output;
        $this->user = $user;
    }

    public function import()
    {
        $strategies = \Config::get( 'ccdimporterstrategiesmaps' );

        $sections = \Config::get( 'ccdimportersections' );
        /**
         * Parse and Import Allergies List, Medications List, Problems List, Problems To Monitor
         */
        foreach ( $this->output as $sectionId => $data ) {

            if ( !is_int( $sectionId ) ) continue;

            if ( empty($data) ) continue;

            //this gets the storage strategy from config/ccdimportersections by sectionId
            $storageStrategy = $strategies[ 'storage' ][ $sections[ $sectionId ][ 'storageIds' ][ 0 ] ];

            if ( class_exists( $storageStrategy ) ) {
                $storage = new $storageStrategy( $this->user->program_id, $this->user );
                $storage->import( $data );
            }
        }

        /**
         * The following Sections are the same for each CCD
         */

        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser( new UserMetaTemplate() );
        ( new UserMetaStorageStrategy( $this->user->program_id, $this->user->ID ) )->import( $this->output[ 'userMeta' ] );

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
}