<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 13/01/2017
 * Time: 12:02 AM
 */

namespace App\Importer\Section\Importers;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Models\CCD\CcdInsurancePolicy;

class Insurance extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
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
        }
    }
}
