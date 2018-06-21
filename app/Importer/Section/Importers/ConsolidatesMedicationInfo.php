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

        $consolidatedMedication->cons_code             = null;
        $consolidatedMedication->cons_code_system      = null;
        $consolidatedMedication->cons_code_system_name = null;
        $consolidatedMedication->cons_name             = null;
        $consolidatedMedication->cons_text             = null;

        if ( ! empty($medicationLog->translation_code)) {
            $consolidatedMedication->cons_code             = $medicationLog->translation_code;
            $consolidatedMedication->cons_code_system      = $medicationLog->translation_code_system;
            $consolidatedMedication->cons_code_system_name = $medicationLog->translation_code_system_name;
            $consolidatedMedication->cons_name             = $medicationLog->reference_title;

            $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
            $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);
            $consolidatedMedication = $this->determineNameSigValidity($consolidatedMedication);

            return $consolidatedMedication;
        }


        $consolidatedMedication->cons_code             = $medicationLog->product_code;
        $consolidatedMedication->cons_code_system      = $medicationLog->product_code_system;
        $consolidatedMedication->cons_code_system_name = $medicationLog->product_code_system_name;
        $consolidatedMedication->cons_name             = $medicationLog->reference_title;

        $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);
        $consolidatedMedication = $this->consolidateSig($consolidatedMedication, $medicationLog);

        $consolidatedMedication = $this->determineNameSigValidity($consolidatedMedication);

        return $consolidatedMedication;
    }

    private function determineNameSigValidity($consolidatedMedication)
    {

        $keywords = $this->keywords();

        $name = $consolidatedMedication->cons_name;
        $sig  = $consolidatedMedication->cons_text;

        //if both fields have the same value it stays the same
        if (str_contains(strtolower($name), $keywords)) {
            $consolidatedMedication->cons_text = $name;
            if ( ! str_contains(strtolower($sig), $keywords)) {
                $consolidatedMedication->cons_name = $sig;
            }
        }

        return $consolidatedMedication;
    }

    private function consolidateName(
        $consolidatedMedication,
        $medicationLog
    ) {
        if (empty($consolidatedMedication->cons_name) || $this->containsKeywords($consolidatedMedication->cons_name)) {

            if ( ! empty($medicationLog->reference_title) || ! $this->containsKeywords($medicationLog->reference_title)) {
                $consolidatedMedication->cons_name = $medicationLog->reference_title;
            } elseif ( ! empty($medicationLog->translation_name) || ! $this->containsKeywords($medicationLog->translation_name)) {
                $consolidatedMedication->cons_name = $medicationLog->translation_name;
            } elseif ( ! empty($medicationLog->product_name) || ! $this->containsKeywords($medicationLog->product_name)) {
                $consolidatedMedication->cons_name = $medicationLog->product_name;
            }
        }

        return $consolidatedMedication;
    }

    private function consolidateSig(
        $consolidatedMedication,
        $medicationLog
    ) {
        if (empty($consolidatedMedication->cons_text || ! $this->containsKeywords($consolidatedMedication->cons_text))) {

            if ( ! empty($medicationLog->reference_sig) || $this->containsKeywords($medicationLog->reference_sig)) {
                $consolidatedMedication->cons_text = $medicationLog->reference_sig;
            } elseif ( ! empty($medicationLog->product_text) || $this->containsKeywords($medicationLog->product_text)) {
                $consolidatedMedication->cons_text = $medicationLog->product_text;
            }
        }

        return $consolidatedMedication;
    }

    private function containsKeywords($field): bool
    {
        return str_contains(strtolower($field), $this->keywords());
    }

    private function keywords(): array
    {

        return $keywords = [
            'daily',
            'weekly',
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
            'rectally',
            //initials
            'aa',
            'ad.',
            'a.c',
            'a.d',
            'ad lib',
            'admov',
            'agit',
            'alt. h.',
            'a.m.',
            'amt',
            'a.l.',
            'a.s.',
            'a.t.c.',
            'a.u.',
            'bis',
            'b.m.',
            'bol.',
            'b.s.',
            'b.s.a',
            'c.',
            'cf',
            'comp.',
            'cr.',
            'crm',
            'd5w',
            'd5ns',
            'd.a.w',
            'd/c',
            'dieb. alt.',
            'd.t.d',
            'dtd',
            'd.w.',
            'elix.',
            'e.m.p',
            'emuls.',
            'ex aq',
            'fl.',
            'fld.',
            'ft.',
            'gtt(s)',
            'h.',
            'hr.',
            'h.s.',
            'id.',
            'im.',
            'iv.',
            'inj.',
            'ip.',
            'ivp.',
            'ivpb',
            'l.a.s.',
            'lcd',
            'lin.',
            'liq.',
            'lot.',
            'min.',
            'mcg',
            'meq.',
            'mist.',
            'mitte',
            'nebul',
            'n.m.t.',
            'noct.',
            'non rep.',
            'ns.',
            '1/2ns',
            'n.t.e',
            'o_2',
            'p.m.',
            'p.r',
            'pulv.',
            'qd',
            'q.d',
            'q.s',
            'p.o',
            'rep.',
            'rept.',
            'i.m',
            'i.v',
            'o.d',
            'o.s',
            'o.u',
            'sc.',
            'sig.',
            'sol.',
            's.o.s',
            'stat',
            'tbsp',
            'troche',
            'tsp',
            'subc',
            'subcut',
            'subq',
            'qhs',
            'q.h.s',
            'qh',
            'q.h.',
            'q3-4h',
            'q.4h',
            'q.6h',
            'q.a.d',
            'q.a.m',
            'q.o.d',
            'qqh',
            'bid',
            'b.i.d',
            'tid.',
            't.i.d',
            'tds',
            't.d.s',
            't.i.w',
            'tiw',
            'tpn',
            't.p.n.',
            'qid',
            'q.i.d',
            '5x',
            'prn',
            'q.t.t',
            'a.c',
            'p.c',
            'u.d.',
            'dict.',
            'vag.',
            'w/o',
        ];

    }
}
