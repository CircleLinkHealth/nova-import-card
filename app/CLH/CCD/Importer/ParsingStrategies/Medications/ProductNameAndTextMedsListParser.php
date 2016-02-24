<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Facades\StringManipulation;

class ProductNameAndTextMedsListParser implements ParsingStrategy
{

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = $ccd->medications;

        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            $medsList .= "\n\n";
            empty($medication->product->name)
                ?: $medsList .= ucfirst( strtolower( $medication->product->name ) ) . ', ';

            $medsList .= ucfirst(
                    strtolower(
                        StringManipulation::stringDiff( $medication->product->name, $medication->text )
                    )
                )
                . ";";
        }
        return $medsList;
    }
}