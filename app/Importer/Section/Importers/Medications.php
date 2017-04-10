<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/01/2017
 * Time: 12:11 AM
 */

namespace App\Importer\Section\Importers;


use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Facades\StringManipulation;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\MedicationGroupsMap;

class Medications extends BaseImporter
{
    use ConsolidatesMedicationInfo;

    protected $medicalRecordId;
    protected $medicalRecordType;
    protected $importedMedicalRecord;

    protected $imported = [];

    public function __construct(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $this->medicalRecordId = $medicalRecordId;
        $this->medicalRecordType = $medicalRecordType;
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

        if (count($this->imported) == 0) {
            $this->processLogs($itemLogs, true);
        }

        return $this->imported;
    }

    /**
     * Loop through the logs and decide what to import
     *
     * @param $itemLogs
     * @param bool $importAll
     */
    public function processLogs(
        $itemLogs,
        $importAll = false
    ) {
        foreach ($itemLogs as $itemLog) {
            if (!$this->validate($itemLog) && !$importAll) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            $consMed = $this->consolidateMedicationInfo($itemLog);

            $this->importMedication($itemLog, $consMed);
        }
    }

    /**
     * Import a single Medication from an Item Log
     *
     * @param MedicationLog $itemLog
     * @param $consolidatedMed
     */
    public function importMedication(
        MedicationLog $itemLog,
        $consolidatedMed
    ) {
        $medicationGroupId = $this->getMedicationGroup($consolidatedMed->cons_name);

        $this->imported[] = MedicationImport::updateOrCreate([
            'medical_record_type'        => $this->medicalRecordType,
            'medical_record_id'          => $this->medicalRecordId,
            'imported_medical_record_id' => $this->importedMedicalRecord->id,
            'vendor_id'                  => $itemLog->vendor_id,
            'name'                       => ucfirst($consolidatedMed->cons_name),
        ], [
            'ccd_medication_log_id'      => $itemLog->id,
            'medication_group_id'        => $medicationGroupId,
            'sig'                        => ucfirst(StringManipulation::stringDiff($consolidatedMed->cons_name,
                $itemLog->cons_text)),
            'code'                       => $consolidatedMed->cons_code,
            'code_system'                => $consolidatedMed->cons_code_system,
            'code_system_name'           => $consolidatedMed->cons_code_system_name,
        ]);
    }

    /**
     * Get the medication group to activate.
     *
     * @param $name
     *
     * @return integer|null
     */
    public function getMedicationGroup($name)
    {
        $maps = MedicationGroupsMap::all();

        foreach ($maps as $map) {
            if (str_contains(strtolower($name), strtolower($map->keyword))) {
                return $map->medication_group_id;
            }
        }

        return null;
    }
}