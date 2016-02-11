<?php

namespace App\CLH\CCD\CarePlanGenerator\Parsers\Medications;


use App\CLH\Contracts\CCD\ParserWithValidation;
use App\CLH\Contracts\CCD\Validator;

class ReferenceTitleAndSigMedsListParser implements ParserWithValidation
{
    public function parse($medicationsSection, Validator $validator)
    {
        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            $medsList .= $medication->reference_title;

            empty($medication->reference_sig)
                ? $medsList .= ''
                : $medsList .= ', ' . $medication->reference_sig;

            $medsList .= "; \n\n";
        }

        return $medsList;
    }
}