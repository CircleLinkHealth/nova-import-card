<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\BaseImporter;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
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
            $insurance = CcdInsurancePolicy::updateOrCreate([
                                                                'medical_record_id'   => $medicalRecordId,
                                                                'medical_record_type' => $medicalRecordType,
                                                                'name'                => $itemLog->name,
                                                                'type'                => $itemLog->type,
                                                                'policy_id'           => $itemLog->policy_id,
                                                                'relation'            => $itemLog->relation,
                                                                'subscriber'          => $itemLog->subscriber,
                                                            ]);
            
            if ($insurance->wasRecentlyCreated) {
                $insurance->approved = false;
                $insurance->save();
            }

            $insurances->push($insurance);
        }

        return $insurances;
    }
}
