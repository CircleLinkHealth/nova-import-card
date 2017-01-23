<?php

namespace App\Importer\Section\Importers;


use App\Importer\Models\ItemLogs\MedicationLog;

trait ConsolidatesMedicationInfo
{
    /**
     * Consolidate Medication info from BB Medication Product and BB Medication Product Translation sections.
     * Sometimes info is in product, or translation or both.
     *
     * @param MedicationLog $medicationLog
     *
     * @return mixed
     */
    private function consolidateMedicationInfo(MedicationLog $medicationLog)
    {
        $consolidatedMedication = new \stdClass();

        $consolidatedMedication->cons_code = null;
        $consolidatedMedication->cons_code_system = null;
        $consolidatedMedication->cons_code_system_name = null;
        $consolidatedMedication->cons_name = null;
        $consolidatedMedication->cons_text = null;

        if (!empty($medicationLog->translation_code)) {
            $consolidatedMedication->cons_code = $medicationLog->translation_code;
            $consolidatedMedication->cons_code_system = $medicationLog->translation_code_system;
            $consolidatedMedication->cons_code_system_name = $medicationLog->translation_code_system_name;
            $consolidatedMedication->cons_name = $medicationLog->translation_name;

            $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
            $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);

            return $consolidatedMedication;
        }


        $consolidatedMedication->cons_code = $medicationLog->product_code;
        $consolidatedMedication->cons_code_system = $medicationLog->product_code_system;
        $consolidatedMedication->cons_code_system_name = $medicationLog->product_code_system_name;
        $consolidatedMedication->cons_name = $medicationLog->product_name;

        $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
        $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);

        return $consolidatedMedication;
    }

    private function consolidateName(
        $consolidatedMedication,
        $medicationLog
    ) {
        if (empty($consolidatedMedication->cons_name)) {
            if (!empty($medicationLog->translation_name)) {
                $consolidatedMedication->cons_name = $medicationLog->translation_name;
            } elseif (!empty($medicationLog->product_name)) {
                $consolidatedMedication->cons_name = $medicationLog->product_name;
            } elseif (!empty($medicationLog->reference_title)) {
                $consolidatedMedication->cons_name = $medicationLog->reference_title;
            }
        }

        return $consolidatedMedication;
    }

    private function consolidateSig(
        $consolidatedMedication,
        $medicationLog
    ) {
        if (empty($consolidatedMedication->cons_text)) {
            if (!empty($medicationLog->reference_sig)) {
                $consolidatedMedication->cons_text = $medicationLog->reference_sig;
            } elseif (!empty($medicationLog->product_text)) {
                $consolidatedMedication->cons_text = $medicationLog->product_text;
            }
        }

        return $consolidatedMedication;
    }
}