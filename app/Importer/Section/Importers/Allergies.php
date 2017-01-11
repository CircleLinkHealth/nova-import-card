<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 7:21 PM
 */

namespace App\Importer\Section\Importers;

use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ItemLogs\AllergyLog;


class Allergies extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType
    ) {
        $itemLogs = AllergyLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->get();

        foreach ($itemLogs as $itemLog) {
            if (!$this->validate($itemLog)) {
                continue;
            }

            if (empty($itemLog->allergen_name)) {
                continue;
            }

            $itemLog->import = true;
            $itemLog->save();

            $allergiesList[] = AllergyImport::create([
                'ccda_id'             => $itemLog->ccda_id,
                'vendor_id'           => $itemLog->vendor_id,
                'ccd_allergy_log_id'  => $itemLog->id,
                'allergen_name'       => $itemLog->allergen_name,
                'medical_record_type' => $medicalRecordType,
                'medical_record_id'   => $medicalRecordId,
            ]);
        }
    }
}