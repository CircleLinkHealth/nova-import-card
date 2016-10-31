<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Medications;


trait ConsolidatesMedicationInfo
{
    /**
     * Consolidate Medication info from BB Medication Product and BB Medication Product Translation sections.
     * Sometimes info is in product, or translation or both.
     *
     * @param $ccdMedication
     *
     * @return mixed
     */
    private function consolidateMedicationInfo($ccdMedication)
    {
        $consolidatedMedication = new \stdClass();

        if (!empty($ccdMedication->translation_code)) {
            $consolidatedMedication->cons_code = $ccdMedication->translation_code;
            $consolidatedMedication->cons_code_system = $ccdMedication->translation_code_system;
            $consolidatedMedication->cons_code_system_name = $ccdMedication->translation_code_system_name;
            $consolidatedMedication->cons_name = $ccdMedication->translation_name;

            if (empty($consolidatedMedication->cons_name) && !empty($ccdMedication->product_name)) {
                $consolidatedMedication->cons_name = $ccdMedication->product_name;
            }

            return $consolidatedMedication;
        }


        $consolidatedMedication->cons_code = $ccdMedication->product_code;
        $consolidatedMedication->cons_code_system = $ccdMedication->product_code_system;
        $consolidatedMedication->cons_code_system_name = $ccdMedication->product_code_system_name;
        $consolidatedMedication->cons_name = $ccdMedication->product_name;

        if (empty($consolidatedMedication->cons_name) && !empty($ccdMedication->translation_name)) {
            $consolidatedMedication->cons_name = $ccdMedication->translation_name;
        }

        return $consolidatedMedication;
    }
}