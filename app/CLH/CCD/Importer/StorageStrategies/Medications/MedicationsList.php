<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies\Medications;

use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;

class MedicationsList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($medsList)
    {
        if (empty($medsList)) {
            return;
        }

        $instruction = CpmInstruction::create([
            'name' => $medsList,
        ]);

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id,
        ]);
    }
}
