<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ItemLogger\CcdMedicationLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Facades\StringManipulation;

class ProductNameAndTextMedsListParser implements ParsingStrategy
{

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = CcdMedicationLog::whereCcdaId($ccd->id)->get();

        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            $medication->import = true;
            $medication->save();

            $medsList[] = $medication;

//            $medsList .= "\n\n";
//            empty($medication->product->name)
//                ?: $medsList .= ucfirst( strtolower( $medication->product->name ) );
//
//            $medsList .= ucfirst(
//                    strtolower(
//                        empty( $medText = StringManipulation::stringDiff( $medication->product->name, $medication->text ) )
//                            ? ';'
//                            : ', ' . $medText . ";"
//                    )
//                );
        }
        return $medsList;
    }
}