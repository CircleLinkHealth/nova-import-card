<?php

namespace App\CLH\CCD\Importer;


use App\CLH\CCD\Importer\StorageStrategies\DefaultSections\TransitionalCare;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserConfigStorageStrategy;
use App\CLH\CCD\Importer\StorageStrategies\Demographics\UserMetaStorageStrategy;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserConfigParser;
use App\CLH\CCD\Importer\ParsingStrategies\Demographics\UserMetaParser;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\ParsedCCD;

class ImportManager
{
    private $blogId;
    private $ccd;
    private $userId;
    private $routine;

    public function __construct($blogId, ParsedCCD $parsedCCD, $userId)
    {
        $this->blogId = $blogId;
        $this->ccd = json_decode( $parsedCCD->ccd );
        $this->userId = $userId;
        $this->routine = ( new RoutineBuilder() )->getDefaultSettings();
    }

    public function generateCarePlanFromCCD()
    {
        /**
         * Parse and Import Allergies List, Medications List, Problems List, Problems To Monitor
         */
        foreach ( $this->routine as $section => $routine ) {
            ImporterStrategyFactory::make( $this->ccd, $routine[ 'validator' ], $routine[ 'parser' ], $routine[ 'importer' ], $this->blogId, $this->userId );
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