<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Importers;

use App\Importer\Models\ItemLogs\InsuranceLog;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;

class Insurance extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $insurances = collect();

        $itemLogs = InsuranceLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get();

        foreach ($itemLogs as $itemLog) {
            $insurance = CcdInsurancePolicy::create([
                'medical_record_id'   => $medicalRecordId,
                'medical_record_type' => $medicalRecordType,
                'name'                => $itemLog->name,
                'type'                => $itemLog->type,
                'policy_id'           => $itemLog->policy_id,
                'relation'            => $itemLog->relation,
                'subscriber'          => $itemLog->subscriber,
                'approved'            => false,
                'import'              => true,
            ]);

            $insurances->push($insurance);
        }

        return $insurances;
    }
}
