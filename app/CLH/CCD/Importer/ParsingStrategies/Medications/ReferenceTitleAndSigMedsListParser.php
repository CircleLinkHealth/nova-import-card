<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class ReferenceTitleAndSigMedsListParser implements ParsingStrategy
{
    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = $ccd->medications;

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