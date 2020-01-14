<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BaseStorageStrategy;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategy;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

class ProblemsList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($problemsList)
    {
        if (empty($problemsList)) {
            return false;
        }

        $instruction = CpmInstruction::create([
            'name' => $problemsList,
        ]);

        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id,
        ]);
    }
}
