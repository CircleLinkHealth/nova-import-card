<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class ProblemsToMonitorStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($cpmProblemNames = [])
    {
        if (empty($cpmProblemNames)) return;
        if (empty($this->blogId) or empty($this->userId)) throw new \Exception('UserID and BlogID are required.');
        $carePlan = CarePlan::where('program_id', '=', $this->blogId)->where('type', '=', 'Program Default')->first();
        if(!$carePlan) {
            return response()->json(["message" => "Careplan Not Found"]);
        }
        foreach ($cpmProblemNames as $cpmProblemName) {
            $carePlan->setCareItemUserValue($this->user, $cpmProblemName,"Active");
        }
    }
}