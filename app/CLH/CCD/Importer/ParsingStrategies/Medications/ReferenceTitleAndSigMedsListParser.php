<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\MedicationImport;
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

            $consMed = $this->consolidateMedicationInfo($medication);

            $importedMed = (new MedicationImport())->create([
                'ccda_id' => $ccd->id,
                'vendor_id' => $ccd->vendor_id,
                'ccd_medication_log_id' => $medication->id,
                'name' => $medication->reference_title,
                'sig' => $medication->reference_sig,
                'code' => $consMed->cons_code,
                'code_system' => $consMed->cons_code_system,
                'code_system_name' => $consMed->cons_code_system_name,
            ]);

            $medsList[] = $importedMed;
        }

        return $medsList;
    }
}