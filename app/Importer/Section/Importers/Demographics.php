<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Importers;

use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ItemLogs\DemographicsLog;
use Carbon\Carbon;

class Demographics extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    ) {
        $itemLog = DemographicsLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->first();

        $demographicsImport = DemographicsImport::updateOrCreate([
            'first_name' => ucwords(strtolower($itemLog->first_name)),
            'last_name'  => ucwords(strtolower($itemLog->last_name)),
            'dob'        => (new Carbon($itemLog->dob, 'America/New_York'))->format('Y-m-d'),
            'gender'     => call_user_func(function () use (
                $itemLog
            ) {
                $maleVariations = [
                    'm',
                    'male',
                    'man',
                ];

                $femaleVariations = [
                    'f',
                    'female',
                    'woman',
                ];

                if (in_array(strtolower($itemLog->gender), $maleVariations)) {
                    $gender = 'M';
                } else {
                    if (in_array(strtolower($itemLog->gender), $femaleVariations)) {
                        $gender = 'F';
                    }
                }

                return empty($gender)
                    ?: $gender;
            }),
            'mrn_number' => $itemLog->mrn_number,
            'street'     => empty($itemLog->street2)
                ? $itemLog->street
                : "{$itemLog->street}, {$itemLog->street2}",
            'city'               => $itemLog->city,
            'state'              => $itemLog->state,
            'zip'                => $itemLog->zip,
            'cell_phone'         => $itemLog->cell_phone,
            'home_phone'         => $itemLog->home_phone,
            'work_phone'         => $itemLog->work_phone,
            'primary_phone'      => $itemLog->primary_phone,
            'email'              => strtolower(str_replace('/', '', $itemLog->email)),
            'study_phone_number' => empty($itemLog->cell_phone)
                ? empty($itemLog->home_phone)
                    ? $itemLog->work_phone
                    : $itemLog->home_phone
                : $itemLog->cell_phone,
            'preferred_contact_language' => call_user_func(function () use (
                $itemLog
            ) {
                $englishVariations = [
                    'english',
                    'eng',
                    'en',
                    'e',
                ];

                $spanishVariations = [
                    'spanish',
                    'es',
                ];

                $default = 'EN';

                if (in_array(strtolower($itemLog->language), $englishVariations)) {
                    $language = 'EN';
                } else {
                    if (in_array(strtolower($itemLog->language), $spanishVariations)) {
                        $language = 'ES';
                    }
                }

                return empty($language)
                    ? $default
                    : $language;
            }),
            'preferred_contact_timezone' => 'America/New_York',
            'consent_date'               => $itemLog->consent_date > 0
                ? Carbon::parse($itemLog->consent_date)->format('Y-m-d')
                : date('Y-m-d'),
            'vendor_id'                  => 1,
            'medical_record_type'        => $medicalRecordType,
            'medical_record_id'          => $medicalRecordId,
            'imported_medical_record_id' => $importedMedicalRecord->id,
            'preferred_call_times'       => $itemLog->preferred_call_times,
            'preferred_call_days'        => $itemLog->preferred_call_days,
        ]);
    }
}
