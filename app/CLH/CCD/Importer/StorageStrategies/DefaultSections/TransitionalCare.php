<?php

namespace App\CLH\CCD\Importer\StorageStrategies\DefaultSections;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\DefaultSectionsImporter;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class TransitionalCare extends BaseStorageStrategy implements DefaultSectionsImporter
{
    public function setDefaults()
    {

        if (!$this->blogId || !$this->user) {
            throw new \Exception('Unable to build careplan');
        }

        $carePlan = CarePlan::where('program_id', '=', $this->blogId)->where('type', '=', 'Program Default')->first();
        if (!$carePlan) {
            throw new \Exception('Unable to build careplan');
        }

        //Make TCC Active, set the days to 5
        $carePlan->setCareItemUserValue($this->user, 'cf-hsp-10-track-care-transitions', "Active");
        $carePlan->setCareItemUserValue($this->user, 'track-care-transitions-contact-days', "5");
    }
}