<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;

use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;

class ProblemsList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($problemsList)
    {
        if (empty($problemsList)) {
            return false;
        }

        $instruction = CpmInstruction::create([
            'name' => $problemsList
        ]);

        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id
        ]);
    }
}
