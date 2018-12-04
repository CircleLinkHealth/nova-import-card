<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Medications;

use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;
use Illuminate\Support\Facades\Log;

class MedicationsList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($medsList)
    {
        if (empty($medsList)) {
            return;
        }
        
        $instruction = CpmInstruction::create([
            'name' => $medsList
        ]);

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id
        ]);
    }
}
