<?php

namespace App\CLH\CCD\CarePlanGenerator;


use App\CLH\CCD\CarePlanGenerator\Importers\DefaultSections\TransitionalCare;
use App\CLH\CCD\CarePlanGenerator\Importers\Demographics\UserConfigImporter;
use App\CLH\CCD\CarePlanGenerator\Importers\Demographics\UserMetaImporter;
use App\CLH\CCD\CarePlanGenerator\Parsers\Demographics\UserConfigParser;
use App\CLH\CCD\CarePlanGenerator\Parsers\Demographics\UserMetaParser;
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
        $this->ccd = json_decode($parsedCCD->ccd);
        $this->userId = $userId;
        $this->routine = (new RoutineBuilder())->getDefaultSettings();
    }

    public function generateCarePlanFromCCD()
    {
        /**
         * Parse and Import User Meta
         */
        $userMetaParser = new UserMetaParser(new UserMetaTemplate());
        $userMeta = $userMetaParser->parse($this->ccd->demographics);
        (new UserMetaImporter($this->blogId, $this->userId))->import($userMeta);

        /**
         * Parse and Import User Config
         */
        $userConfigParser = new UserConfigParser(new UserConfigTemplate(), $this->blogId);
        $userConfig = $userConfigParser->parse($this->ccd->demographics);
        (new UserConfigImporter($this->blogId, $this->userId))->import($userConfig);

        /**
         * Parse and Import Allergies List
         */
        $allergiesParser = new $this->routine['allergiesList']['parser']();
        $allergies = $allergiesParser->parse($this->ccd->allergies, new $this->routine['allergiesList']['validator']());
        (new $this->routine['allergiesList']['importer']($this->blogId, $this->userId))->import($allergies);

        /**
         * Parse and Import Medications List
         */
        $medicationsListParser = new $this->routine['medicationsList']['parser']();
        $medications = $medicationsListParser->parse($this->ccd->medications, new $this->routine['medicationsList']['validator']());
        (new $this->routine['medicationsList']['importer']($this->blogId, $this->userId))->import($medications);

        /**
         * Parse and Import Problems List
         */
        $problemsListParser = new $this->routine['problemsList']['parser']();
        $problemsList = $problemsListParser->parse($this->ccd->problems, new $this->routine['problemsList']['validator']());
        (new $this->routine['problemsList']['importer']($this->blogId, $this->userId))->import($problemsList);

        /**
         * Parse and Import Problems To Monitor
         */
        $problemsToMonitorParser = new $this->routine['problemsToMonitor']['parser']();
        $problemsToMonitor = $allergiesParser->parse($this->ccd->problems, new $this->routine['problemsToMonitor']['validator']());
        (new $this->routine['problemsToMonitor']['importer']($this->blogId, $this->userId))->import($allergies);

        /**
         * CarePlan Defaults
         */
        $transitionalCare = new TransitionalCare($this->blogId, $this->userId);
        $transitionalCare->setDefaults();
    }
}