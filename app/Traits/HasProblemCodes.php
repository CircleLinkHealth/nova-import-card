<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 10/24/2017
 * Time: 12:02 PM
 */

namespace App\Traits;


use App\Constants;

trait HasProblemCodes
{
    public function icd9Codes() {
        return $this->codes()
            ->where('code_system_oid', '=', '2.16.840.1.113883.6.103')
            ->orWhere([
                ['code_system_name', 'like', '%9%'],
                ['code_system_name', 'like', '%icd%'],
            ]);
    }

    public function icd10Codes() {
        return $this->codes()
            ->where('code_system_oid', '=', '2.16.840.1.113883.6.3')
            ->orWhere([
                ['code_system_name', 'like', '%10%'],
                ['code_system_name', 'like', '%icd%'],
            ]);
    }

    public function snomedCodes() {
        return $this->codes()
            ->where('code_system_oid', '=', '2.16.840.1.113883.6.96')
            ->orWhere([
                ['code_system_name', 'like', '%snomed%'],
            ]);
    }

    public function codeMap() {
        $map = collect();

        $icd9 = $this->icd9Codes->first();
        if ($icd9) {
            $map[Constants::ICD9] = $icd9->code;
        }

        $icd10 = $this->icd10Codes->first();
        if ($icd10) {
            $map[Constants::ICD10] = $icd10->code;
        }

        $snomed = $this->snomedCodes->first();
        if ($snomed) {
            $map[Constants::SNOMED] = $snomed->code;
        }

        return $map;
    }
}