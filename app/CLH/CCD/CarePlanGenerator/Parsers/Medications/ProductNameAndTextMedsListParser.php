<?php

namespace App\CLH\CCD\CarePlanGenerator\Parsers\Medications;


use App\CLH\Contracts\CCD\ParserWithValidation;
use App\CLH\Contracts\CCD\Validator;
use App\CLH\Facades\StringManipulation;

class ProductNameAndTextMedsListParser implements ParserWithValidation
{

    public function parse($medicationsSection, Validator $validator)
    {
        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            empty($medication->product->name)
                ? $medsList .= ''
                : $medsList .= ucfirst( strtolower( $medication->product->name ) ) . ', ';

            $medsList .= ucfirst(
                    strtolower(
                        StringManipulation::stringDiff( $medication->product->name, $medication->text )
                    )
                )
                . "; \n\n";
        }
        return $medsList;
    }
}