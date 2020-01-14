<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Importers;

use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use CircleLinkHealth\CarePlanModels\Entities\MedicationImport;
use CircleLinkHealth\CarePlanModels\Entities\MedicationLog;
use App\MedicationGroupsMap;

class Medications extends BaseImporter
{
    use ConsolidatesMedicationInfo;

    protected $imported = [];
    protected $importedMedicalRecord;

    protected $medicalRecordId;
    protected $medicalRecordType;

    public function __construct(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $this->medicalRecordId       = $medicalRecordId;
        $this->medicalRecordType     = $medicalRecordType;
        $this->importedMedicalRecord = $importedMedicalRecord;
    }

    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $itemLogs = MedicationLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get();

        $this->processLogs($itemLogs);

        if (0 == count($this->imported)) {
            $this->processLogs($itemLogs, true);
        }

        return $this->imported;
    }

    /**
     * Import a single Medication from an Item Log.
     *
     * @param \CircleLinkHealth\CarePlanModels\Entities\MedicationLog $itemLog
     * @param $consolidatedMed
     */
    public function importMedication(
        MedicationLog $itemLog,
        $consolidatedMed
    ) {
        $medicationGroupId = MedicationGroupsMap::getGroup($consolidatedMed->cons_name) ?? MedicationGroupsMap::getGroup($consolidatedMed->cons_text);

        $this->imported[] = MedicationImport::updateOrCreate([
            'medical_record_type'        => $this->medicalRecordType,
            'medical_record_id'          => $this->medicalRecordId,
            'imported_medical_record_id' => $this->importedMedicalRecord->id,
            'vendor_id'                  => $itemLog->vendor_id,
            'name'                       => ucfirst($consolidatedMed->cons_name),
        ], [
            'ccd_medication_log_id' => $itemLog->id,
            'medication_group_id'   => $medicationGroupId,
            'sig'                   => ucfirst((new \CircleLinkHealth\Core\StringManipulation())->stringDiff(
                $consolidatedMed->cons_name,
                $consolidatedMed->cons_text
            )),
            'code'             => $consolidatedMed->cons_code,
            'code_system'      => $consolidatedMed->cons_code_system,
            'code_system_name' => $consolidatedMed->cons_code_system_name,
        ]);
    }

    /**
     * Loop through the logs and decide what to import.
     *
     * @param $itemLogs
     * @param bool $importAll
     */
    public function processLogs(
        $itemLogs,
        $importAll = false
    ) {
        foreach ($itemLogs as $itemLog) {
            if ( ! $this->validate($itemLog) && ! $importAll) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            $consMed = $this->consolidateMedicationInfo($itemLog);

            if ( ! $this->containsSigKeywords($consMed->cons_name)) {
                $this->importMedication($itemLog, $consMed);
            }
        }
    }
}
