<?php

namespace App\CLH\CCD\CarePlanGenerator\Parsers\Allergies;


use App\CLH\Contracts\CCD\ParserWithValidation;
use App\CLH\Contracts\CCD\Validator;

class AllergenNameAllergiesListParser implements ParserWithValidation
{

    public function parse($ccd, Validator $validator)
    {
        $ccdAllergiesSection = $ccd->allergies;
        $allergiesList = '';

        foreach ( $ccdAllergiesSection as $ccdAllergy ) {
            if ( !$validator->validate( $ccdAllergy ) ) continue;

            $ccdAllergen = $ccdAllergy->allergen;

            if ( empty($ccdAllergen->name) ) continue;

            $allergiesList .= ucfirst( strtolower( $ccdAllergen->name ) ) . ";\n\n";
        }

        return $allergiesList;
    }
}