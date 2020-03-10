<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\BaseImporter;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\AllergyImport;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;

class Allergies extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $itemLogs = AllergyLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get()
            ->unique('allergen_name')
            ->values();

        foreach ($itemLogs as $itemLog) {
            if ( ! $this->validate($itemLog)) {
                continue;
            }

            if (empty($itemLog->allergen_name)) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            $allergiesList[] = AllergyImport::updateOrCreate([
                'vendor_id'                  => $itemLog->vendor_id,
                'ccd_allergy_log_id'         => $itemLog->id,
                'allergen_name'              => $itemLog->allergen_name,
                'medical_record_type'        => $medicalRecordType,
                'medical_record_id'          => $medicalRecordId,
                'imported_medical_record_id' => $importedMedicalRecord->id,
            ]);
        }
    }
}
