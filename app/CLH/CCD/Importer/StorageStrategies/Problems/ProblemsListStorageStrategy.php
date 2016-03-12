<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class ProblemsListStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($problemsList)
    {
        if ( empty($problemsList) ) return false;

        $carePlan = CarePlan::where('program_id', '=', $this->blogId)->where('type', '=', 'Program Default')->first();

        if(!$carePlan) {
            throw new \Exception('Unable to build careplan');
        }

        $carePlan->setCareItemUserValue($this->user, 'other-conditions-details', $problemsList);
        $carePlan->setCareItemUserValue($this->user, 'other-conditions',"Active");
    }
}