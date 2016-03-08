<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ItemLogger\CcdMedicationLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;

class ReferenceTitleAndSigMedsListParser implements ParsingStrategy
{
    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = CcdMedicationLog::whereCcdaId($ccd->id)->get();;

        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            $medication->import = true;
            $medication->save();

            $medsList[] = $medication;
//            $medsList .= $medication->reference_title;
//
//            empty($medication->reference_sig)
//                ? $medsList .= ''
//                : $medsList .= ', ' . $medication->reference_sig;
//
//            $medsList .= "; \n\n";
        }

        return $medsList;
    }
}