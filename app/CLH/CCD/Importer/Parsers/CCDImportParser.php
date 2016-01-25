<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;

class CCDImportParser extends BaseParser
{
    public function parse()
    {
        $blogId = $this->blogId;
        $parsedCCD = $this->parsedCcdObj;

        /**
         * Import Allergies
         */
        $allergiesParser = new AllergiesParser($blogId, $parsedCCD);
        $allergiesList = $allergiesParser->parse();
        $allergiesParser->save($allergiesList);

        /**
         * Import Medications
         */
        $medsParser = new MedicationsParser($blogId, $parsedCCD);
        $medsList = $medsParser->parse();
        $medsParser->save($medsList);

        /**
         * Import Problems
         */
        $problemsParser = new ProblemsParser($blogId, $parsedCCD);
        $problemsList = $problemsParser->parse();
        $problemsParser->save($problemsList);

        $problemsParser->activateCPProblems();

        /**
         * Import User Config
         */
        $userConfigParser = new UserConfigParser($blogId, $parsedCCD, new UserConfigTemplate());
        $userConfig =  $userConfigParser->parse()->getArray();
        $userConfigParser->save($userConfig);

        /**
         * Import User Meta
         */
        $userMetaParser = new UserMetaParser($blogId, $parsedCCD, new UserMetaTemplate());
        $userMeta = $userMetaParser->parse()->getArray();
        $userMetaParser->save($userMeta);
    }

    public function save($data)
    {
        // TODO: Implement save() method.
    }
}