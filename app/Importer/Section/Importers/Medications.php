<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/01/2017
 * Time: 12:11 AM
 */

namespace App\Importer\Section\Importers;


use App\CLH\Facades\StringManipulation;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ItemLogs\MedicationLog;

class Medications extends BaseImporter
{
    use ConsolidatesMedicationInfo;

    public function import(
        $medicalRecordId,
        $medicalRecordType
    ) {
        $itemLogs = MedicationLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get();

        $medsList = [];

        foreach ($itemLogs as $itemLog) {
            if (!$this->validate($itemLog)) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            $consMed = $this->consolidateMedicationInfo($itemLog);

            $medsList[] = (new MedicationImport())->updateOrCreate([
                'medical_record_type'   => $medicalRecordType,
                'medical_record_id'     => $medicalRecordId,
                'ccda_id'               => $medicalRecordId,
                'vendor_id'             => $itemLog->vendor_id,
                'ccd_medication_log_id' => $itemLog->id,
                'name'                  => ucfirst($consMed->cons_name),
                'sig'                   => ucfirst(StringManipulation::stringDiff($consMed->cons_name,
                    $itemLog->cons_text)),
                'code'                  => $consMed->cons_code,
                'code_system'           => $consMed->cons_code_system,
                'code_system_name'      => $consMed->cons_code_system_name,
            ]);
        }

        return $medsList;
    }
}