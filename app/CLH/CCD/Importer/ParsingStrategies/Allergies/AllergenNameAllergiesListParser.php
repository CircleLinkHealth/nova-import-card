<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Allergies;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class AllergenNameAllergiesListParser implements ParsingStrategy
{

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $ccdAllergiesSection = CcdAllergyLog::whereCcdaId($ccd->id)->get();
        $allergiesList = '';

        foreach ( $ccdAllergiesSection as $ccdAllergy )
        {
            if ( !$validator->validate( $ccdAllergy ) ) continue;

//            if ( empty($ccdAllergy->allergen_name) ) continue;

            $ccdAllergy->import = true;
            $ccdAllergy->save();
            $allergiesList[] = $ccdAllergy;
//            $allergiesList .= "\n\n";
//            $allergiesList .= ucfirst( strtolower( $ccdAllergy->allergen_name ) ) . ";";
        }

        return $allergiesList;
    }
}