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
            $consolidatedMedication->cons_name = $medicationLog->reference_title;

            $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
            $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);

            return $consolidatedMedication;
        }


        $consolidatedMedication->cons_code = $medicationLog->product_code;
        $consolidatedMedication->cons_code_system = $medicationLog->product_code_system;
        $consolidatedMedication->cons_code_system_name = $medicationLog->product_code_system_name;
        $consolidatedMedication->cons_name = $medicationLog->reference_title;

        $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
        $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);

        $consolidatedMedication = $this->determineNameSigValidity($consolidatedMedication);

        return $consolidatedMedication;
    }

    private function determineNameSigValidity($consolidatedMedication){

        $keywords = [
            'daily',
            'spoon',
            'once',
            'twice',
            'thrice',
            'four',
            'time',
            'times',
            'day',
            'days',
            'after',
            'food',
            'pill',
            'apply',
            'nostril',
            'doses',
            'puffs',
            'every',
            'sleep',
            'skin',
            'bedtime',
            'needed',
            'food',
            'use',
            'mouth',
            'rinse',
            'each',
            'eye',
            'apply',
            'directed',
            //initials
            'qd',
            'po',
            'sq',
            'qhs',
            'qh',
            'q3-4h',
            'bid',
            'qid',
            'hs',
            'prn',
            'inh',

        ];

        $name = $consolidatedMedication->cons_name;
        $sig  = $consolidatedMedication->cons_text;

        //if both fields have the same value it stays the same
        if (str_contains(strtolower($name), $keywords)){
            $consolidatedMedication->cons_text = $name;
            if (!str_contains(strtolower($sig), $keywords)){
                $consolidatedMedication->cons_name = $sig;
            }
        }

        return $consolidatedMedication;
    }

    private function consolidateName(
        $consolidatedMedication,
        $medicationLog
    ) {
        if (empty($consolidatedMedication->cons_name)) {
            if (!empty($medicationLog->reference_title)) {
                $consolidatedMedication->cons_name = $medicationLog->reference_title;
            } elseif (!empty($medicationLog->translation_name)) {
                $consolidatedMedication->cons_name = $medicationLog->translation_name;
            } elseif (!empty($medicationLog->product_name)) {
                $consolidatedMedication->cons_name = $medicationLog->product_name;
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
