<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Allergies;

use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;
use Illuminate\Support\Facades\Log;

class AllergiesList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($allergiesList)
    {
        if ( empty($allergiesList) ) return;

        if ( empty($this->blogId) or empty($this->user) ) throw new \Exception( 'UserID and BlogID are required.' );

        $instruction = CpmInstruction::create([
            'name' => $allergiesList
        ]);
        
        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id
        ]);
    }
}