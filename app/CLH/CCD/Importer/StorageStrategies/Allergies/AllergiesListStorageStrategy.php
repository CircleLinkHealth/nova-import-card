<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Allergies;

use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class AllergiesListStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($allergiesList)
    {
        if (empty($allergiesList)) return;

        if (empty($this->blogId) or empty($this->user)) throw new \Exception('UserID and BlogID are required.');

        $carePlan = CarePlan::where('program_id', '=', $this->blogId)->where('type', '=', 'Program Default')->first();
        if(!$carePlan) {
            throw new \Exception('Unable to build careplan');
        }

        $carePlan->setCareItemUserValue($this->user, 'allergies-details', $allergiesList);
        $carePlan->setCareItemUserValue($this->user, 'allergies', 'Active');

        $this->user->care_plan_id = $carePlan->id;
        $this->user->save();
    }
}