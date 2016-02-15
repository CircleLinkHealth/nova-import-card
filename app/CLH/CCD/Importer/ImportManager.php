<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfigStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMetaStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMetaParser;
use App\CLH\CCD\ImportRoutine\ExecutesImportRoutine;
use App\CLH\CCD\ImportRoutine\RoutineBuilder;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\ParsedCCD;

class ImportManager
{
    use ExecutesImportRoutine;

    private $blogId;
    private $ccd;
    private $userId;
    private $routine;

    public function __construct($blogId, ParsedCCD $parsedCCD, $userId)
    {
        $this->blogId = $blogId;
        $this->ccd = json_decode( $parsedCCD->ccd );
        $this->userId = $userId;
        $this->routine = ( new RoutineBuilder( $this->ccd ) )->getRoutine();
    }

    public function generateCarePlanFromCCD()
    {
        $strategies = \Config::get( 'ccdimporterstrategiesmaps' );
        /**
         * Parse and Import Allergies List, Medications List, Problems List, Problems To Monitor
         */
        foreach ( $this->routine as $routine ) {
            $this->import( $this->ccd,
                $strategies[ 'validation' ][ $routine->validator_id ],
                $strategies[ 'parsing' ][ $routine->parser_id ],
                $strategies[ 'storage' ][ $routine->storage_id ],
                $this->blogId, $this->userId );
        }

        /**
         * The following Sections are the same for each CCD
         */

        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser( new UserMetaTemplate() );
        $userMeta = $userMetaParser->parse( $this->ccd );
        ( new UserMetaStorageStrategy( $this->blogId, $this->userId ) )->import( $userMeta );

        /**
         * Parse and Import User Config
         */
        $userConfigParser = new UserConfigParser( new UserConfigTemplate(), $this->blogId );
        $userConfig = $userConfigParser->parse( $this->ccd );
        ( new UserConfigStorageStrategy( $this->blogId, $this->userId ) )->import( $userConfig );

        /**
         * CarePlan Defaults
         */
        $transitionalCare = new TransitionalCare( $this->blogId, $this->userId );
        $transitionalCare->setDefaults();
    }
}