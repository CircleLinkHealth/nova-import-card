<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

trait ConsolidatesMedicationInfo
{
    public function containsSigKeywords($field): bool
    {
        return $this->containsExact($field, $this->sigKeywords());
    }

    /**
     * Consolidate Medication info from BB Medication Product and BB Medication Product Translation sections.
     * Sometimes info is in product, or translation or both.
     *
     * @return mixed
     */
    private function consolidateMedicationInfo(object $medicationLog)
    {
        $consolidatedMedication = new \stdClass();

        $consolidatedMedication->cons_code             = null;
        $consolidatedMedication->cons_code_system      = null;
        $consolidatedMedication->cons_code_system_name = null;
        $consolidatedMedication->cons_name             = null;
        $consolidatedMedication->cons_text             = null;
        $consolidatedMedication->status                = $medicationLog->status ?? null;
        $consolidatedMedication->start                 = $medicationLog->start ?? null;
        $consolidatedMedication->end                   = $medicationLog->end ?? null;

        if ( ! empty($medicationLog->translation_code)) {
            $consolidatedMedication->cons_code             = $medicationLog->translation_code;
            $consolidatedMedication->cons_code_system      = $medicationLog->translation_code_system;
            $consolidatedMedication->cons_code_system_name = $medicationLog->translation_code_system_name;
            $consolidatedMedication->cons_name             = trim($medicationLog->reference_title);

            $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);

            return $this->consolidateSig($consolidatedMedication, $medicationLog);
        }

        $consolidatedMedication->cons_code             = $medicationLog->product_code;
        $consolidatedMedication->cons_code_system      = $medicationLog->product_code_system;
        $consolidatedMedication->cons_code_system_name = $medicationLog->product_code_system_name ?? null;
        $consolidatedMedication->cons_name             = trim($medicationLog->reference_title);

        $consolidatedMedication = $this->consolidateName($consolidatedMedication, $medicationLog);

        return $this->consolidateSig($consolidatedMedication, $medicationLog);
    }

    private function consolidateName(
        $consolidatedMedication,
        $medicationLog
    ) {
        collect([
            $medicationLog->product_name,
            $medicationLog->reference_title,
            $medicationLog->translation_name,
        ])->each(function ($c) use (&$consolidatedMedication) {
            if ( ! empty($c) && ! $this->containsSigKeywords($c)) {
                $consolidatedMedication->cons_name = trim($c);

                return false;
            }
        });

        return $consolidatedMedication;
    }

    private function consolidateSig(
        $consolidatedMedication,
        $medicationLog
    ) {
        collect([
            $medicationLog->reference_sig,
            $medicationLog->product_text,
            $medicationLog->text,
            $medicationLog->reference_title,
        ])->each(function ($c) use (&$consolidatedMedication) {
            if ( ! empty($c) && $this->containsSigKeywords($c)) {
                $consolidatedMedication->cons_text = trim($c);

                return false;
            }
        });

        return $consolidatedMedication;
    }

    private function containsExact($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (preg_match("~\\b{$needle}\\b~", $haystack)) {
                return true;
            }
        }

        return false;
    }

    private function sigKeywords(): array
    {
        return $keywords = [
            'take',
            'daily',
            'weekly',
            'spoon',
            'once',
            'one',
            'twice',
            'thrice',
            'four',
            'five',
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
            'ad\.',
            'a\.c',
            'a\.d',
            'ad lib',
            'admov',
            'agit',
            'alt\. h\.',
            'a\.m\.',
            'amt',
            'a\.l\.',
            'a\.s\.',
            'a\.t\.c\.',
            'a\.u\.',
            'bis',
            'b\.m\.',
            'bol\.',
            'b\.s\.',
            'b\.s\.a',
            'c\.',
            'cf',
            'comp\.',
            'cr\.',
            'crm',
            'd5w',
            'd5ns',
            'd\.a\.w',
            'd/c',
            'dieb\. alt\.',
            'd\.t\.d',
            'dtd',
            'd\.w\.',
            'elix\.',
            'e\.m\.p',
            'emuls\.',
            'ex aq',
            'fl\.',
            'fld\.',
            'ft\.',
            'gtt(s)',
            'h\.',
            'hr\.',
            'h\.s\.',
            'id\.',
            'im\.',
            'iv\.',
            'inj\.',
            'ip\.',
            'ivp\.',
            'ivpb',
            'l\.a\.s\.',
            'lcd',
            'lin\.',
            'liq\.',
            'lot\.',
            'min\.',
            'meq\.',
            'mist\.',
            'mitte',
            'nebul',
            'n\.m\.t\.',
            'noct\.',
            'non rep\.',
            'ns\.',
            '1/2ns',
            'n\.t\.e',
            'o_2',
            'p\.m\.',
            'p\.r',
            'pulv\.',
            'qd',
            'q\.d',
            'q\.s',
            'p\.o',
            'rep\.',
            'rept\.',
            'i\.m',
            'i\.v',
            'o\.d',
            'o\.s',
            'o\.u',
            'sc\.',
            'sig\.',
            'sol\.',
            's\.o\.s',
            'stat',
            'tbsp',
            'troche',
            'tsp',
            'subc',
            'subcut',
            'subq',
            'qhs',
            'q\.h\.s',
            'qh',
            'q\.h\.',
            'q3-4h',
            'q\.4h',
            'q\.6h',
            'q\.a\.d',
            'q\.a\.m',
            'q\.o\.d',
            'qqh',
            'bid',
            'b\.i\.d',
            'tid\.',
            't\.i\.d',
            'tds',
            't\.d\.s',
            't\.i\.w',
            'tiw',
            'tpn',
            't\.p\.n\.',
            'qid',
            'q\.i\.d',
            '5x',
            'prn',
            'q\.t\.t',
            'a\.c',
            'p\.c',
            'u\.d\.',
            'dict\.',
            'vag\.',
            'w/o',
        ];
    }
}
