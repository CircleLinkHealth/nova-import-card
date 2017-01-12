<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/01/2017
 * Time: 1:05 AM
 */

namespace App\Importer\Section\Importers;


use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ItemLogs\DemographicsLog;
use Carbon\Carbon;

class Demographics extends BaseImporter
{
    public function import(
        $medicalRecordId,
        $medicalRecordType
    ) {
        $itemLog = DemographicsLog::where('medical_record_type', '=', $medicalRecordType)
            ->where('medical_record_id', '=', $medicalRecordId)
            ->first();

        $demographicsImport = new DemographicsImport();

        $demographicsImport->first_name = ucwords(strtolower($itemLog->first_name));
        $demographicsImport->last_name = ucwords(strtolower($itemLog->last_name));
        $demographicsImport->dob = (new Carbon($itemLog->dob, 'America/New_York'))->format('Y-m-d');
        $demographicsImport->gender = call_user_func(function () use
        (
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
        });
        $demographicsImport->mrn_number = $itemLog->mrn_number;
        $demographicsImport->street = empty($itemLog->street2)
            ? $itemLog->street
            : $itemLog->street . '' . $itemLog->street2;
        $demographicsImport->city = $itemLog->city;
        $demographicsImport->state = $itemLog->state;
        $demographicsImport->zip = $itemLog->zip;
        $demographicsImport->cell_phone = $itemLog->cell_phone;
        $demographicsImport->home_phone = $itemLog->home_phone;
        $demographicsImport->work_phone = $itemLog->work_phone;
        $demographicsImport->email = strtolower(str_replace('/', '', $itemLog->email));
        $demographicsImport->study_phone_number = empty($itemLog->cell_phone)
            ? empty($itemLog->home_phone)
                ? $itemLog->work_phone
                : $itemLog->home_phone
            : $itemLog->cell_phone;
        $demographicsImport->preferred_contact_language = call_user_func(function () use
        (
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

            if (in_array(strtolower($itemLog->preferred_contact_language), $englishVariations)) {
                $language = 'EN';
            } else {
                if (in_array(strtolower($itemLog->preferred_contact_language), $spanishVariations)) {
                    $language = 'ES';
                }
            }

            return empty($language)
                ? $default
                : $language;
        });
        $demographicsImport->preferred_contact_timezone = 'America/New_York';
        $demographicsImport->consent_date = date("Y-m-d");
        $demographicsImport->vendor_id = 1;
        $demographicsImport->ccda_id = $medicalRecordId;
        $demographicsImport->medical_record_type = $medicalRecordType;
        $demographicsImport->medical_record_id = $medicalRecordId;
        $demographicsImport->save();
    }
}