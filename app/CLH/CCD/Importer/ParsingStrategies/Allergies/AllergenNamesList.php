<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Allergies;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Models\MedicalRecords\Ccda;

class AllergenNamesList implements ParsingStrategy
{

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $ccdAllergiesSection = AllergyLog::whereCcdaId($ccd->id)->get();
        $allergiesList = [];

        foreach ( $ccdAllergiesSection as $ccdAllergyLog )
        {
            if ( !$validator->validate( $ccdAllergyLog ) ) continue;

            if ( empty($ccdAllergyLog->allergen_name) ) continue;

            $ccdAllergyLog->import = true;
            $ccdAllergyLog->save();
            
            $importedAllergy = new AllergyImport();

            $allergiesList[] = $importedAllergy->create([
                'ccda_id' => $ccdAllergyLog->ccda_id,
                'vendor_id' => $ccdAllergyLog->vendor_id,
                'ccd_allergy_log_id' => $ccdAllergyLog->id,
                'allergen_name' => $ccdAllergyLog->allergen_name
            ]);
        }

        return $allergiesList;
    }
}