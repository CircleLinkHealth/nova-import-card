<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\Facades\StringManipulation;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Models\MedicalRecords\Ccda;

class ProductNameAndTextList implements ParsingStrategy
{
    use ConsolidatesMedicationInfo;

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $medicationsSection = MedicationLog::whereCcdaId($ccd->id)->get();

        $medsList = '';

        foreach ( $medicationsSection as $medication ) {
            if ( !$validator->validate( $medication ) ) continue;

            $medication->import = true;
            $medication->save();

            $consMed = $this->consolidateMedicationInfo($medication);

            $medsList[] = (new MedicationImport())->updateOrCreate([
                'ccda_id' => $ccd->id,
                'vendor_id' => $ccd->vendor_id,
                'ccd_medication_log_id' => $medication->id,
                'name' => ucfirst( $consMed->cons_name ),
                'sig' => ucfirst( StringManipulation::stringDiff( $consMed->cons_name, $medication->text ) ),
                'code' => $consMed->cons_code,
                'code_system' => $consMed->cons_code_system,
                'code_system_name' => $consMed->cons_code_system_name,
            ]);
        }
        return $medsList;
    }
}