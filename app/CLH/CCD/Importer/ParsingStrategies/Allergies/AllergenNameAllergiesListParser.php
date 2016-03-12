<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Allergies;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class AllergenNameAllergiesListParser implements ParsingStrategy
{

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $ccdAllergiesSection = CcdAllergyLog::whereCcdaId($ccd->id)->get();
        $allergiesList = '';

        foreach ( $ccdAllergiesSection as $ccdAllergyLog )
        {
            if ( !$validator->validate( $ccdAllergyLog ) ) continue;

//            if ( empty($ccdAllergyLog->allergen_name) ) continue;

            $ccdAllergyLog->import = true;
            $ccdAllergyLog->save();

            $importedAllergy = new AllergyImport();

            $allergiesList[] = $importedAllergy->create([
                'ccda_id' => $ccdAllergyLog->ccda_id,
                'vendor_id' => $ccdAllergyLog->vendor_id,
                'ccd_allergy_log_id' => $ccdAllergyLog->id,
                'allergen_name' => $ccdAllergyLog->allergen_name
            ]);
//            $allergiesList .= "\n\n";
//            $allergiesList .= ucfirst( strtolower( $ccdAllergy->allergen_name ) ) . ";";
        }

        return $allergiesList;
    }
}