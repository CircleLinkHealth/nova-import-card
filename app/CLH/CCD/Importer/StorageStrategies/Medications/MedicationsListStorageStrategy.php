<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Medications;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class MedicationsListStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{

    public function import($medsList)
    {
        $carePlan = CarePlan::where('program_id', '=', $this->blogId)->where('type', '=', 'Program Default')->first();
        if(!$carePlan) {
            throw new \Exception('Unable to build careplan');
        }
        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');

        $carePlan->setCareItemUserValue($this->user, 'medications-list-details', $medsList);
        $carePlan->setCareItemUserValue($this->user, 'medications-list',"Active");
    }
}