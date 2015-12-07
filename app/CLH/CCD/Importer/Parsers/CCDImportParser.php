<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;

class CCDImportParser extends BaseParser
{
    public function parse()
    {
        $ccd = new \stdClass();

        $blogId = $this->blogId;
        $parsedCCD = $this->parsedCcdObj;

        /**
         * MedicationsParser is actually an Importer
         * @todo: come back here to clean up
         */
        (new MedicationsParser($blogId, $parsedCCD))->parse();
        $ccd->userConfig =  (new UserConfigParser($blogId, $parsedCCD, new UserConfigTemplate()))->parse()->getArray();
        $ccd->userMeta = (new UserMetaParser($blogId, $parsedCCD, new UserMetaTemplate()))->parse()->getArray();

        return $ccd;
    }
}