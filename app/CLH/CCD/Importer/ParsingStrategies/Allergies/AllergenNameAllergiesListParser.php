<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Allergies;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class AllergenNameAllergiesListParser implements ParsingStrategy
{

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $ccdAllergiesSection = $ccd->allergies;
        $allergiesList = '';

        foreach ( $ccdAllergiesSection as $ccdAllergy ) {
            if ( !$validator->validate( $ccdAllergy ) ) continue;

            $ccdAllergen = $ccdAllergy->allergen;

            if ( empty($ccdAllergen->name) ) continue;

            $allergiesList .= "\n\n";
            $allergiesList .= ucfirst( strtolower( $ccdAllergen->name ) ) . ";";
        }

        return $allergiesList;
    }
}