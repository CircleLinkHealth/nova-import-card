<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ItemLogger\MedicationLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Facades\StringManipulation;
use App\Models\CCD\Ccda;

class ReferenceTitleAndSig implements ParsingStrategy
{
    use ConsolidatesMedicationInfo;

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = MedicationLog::whereCcdaId($ccd->id)->get();;

        $medsList = '';

        foreach ($medicationsSection as $medication) {
            if (!$validator->validate($medication)) continue;

            $medication->import = true;
            $medication->save();

            $consMed = $this->consolidateMedicationInfo($medication);

            $medsList[] = (new MedicationImport())->updateOrCreate([
                'ccda_id' => $ccd->id,
                'vendor_id' => $ccd->vendor_id,
                'ccd_medication_log_id' => $medication->id,
                'name' => ucfirst( $medication->reference_title ),
                'sig' => ucfirst( StringManipulation::stringDiff( $medication->reference_title, $medication->reference_sig ) ),
                'code' => $consMed->cons_code,
                'code_system' => $consMed->cons_code_system,
                'code_system_name' => $consMed->cons_code_system_name,
            ]);
        }

        return $medsList;
    }
}