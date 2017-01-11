<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 7:21 PM
 */

namespace App\Importer\Section\Importers;

use App\Contracts\Importer\MedicalRecord\Section\Importer as SectionImporter;
use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Contracts\Importer\MedicalRecord\Section\Validator;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ItemLogs\AllergyLog;


class Allergies implements SectionImporter
{

    public function import(
        $healthRecordId,
        $healthRecordType
    ) {
        $itemLogs = AllergyLog::where('medical_record_type', '=', $healthRecordType)
            ->where('medical_record_id', '=', $healthRecordId)
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
                'medical_record_type' => $healthRecordType,
                'medical_record_id'   => $healthRecordId,
            ]);
        }
    }

    public function validate(ItemLog $item)
    {
        $validator = $this->chooseValidator($item);

        if (!$validator) {
            return false;
        }

        return $validator->isValid($item);
    }

    public function chooseValidator(ItemLog $item)
    {
        foreach ($this->validators() as $className) {

            $validator = app($className);

            if ($validator->shouldValidate($item)) {
                return $validator;
            }
        }

        return false;
    }

    /**
     * @return Validator[]
     */
    public function validators() : array
    {
        return \Config::get('importer')['validators'];
    }

}