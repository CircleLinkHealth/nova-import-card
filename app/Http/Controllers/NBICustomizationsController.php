<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class NBICustomizationsController extends Controller
{
    public function loadMrnsFromFile()
    {
        $names = collect(parseCsvToArray(storage_path('list.csv')))->unique('mrn');

        $notFound = [];
        $sameMrn  = [];
        $matched  = [];
        foreach ($names as $n) {
            $fullName  = explode(',', $n['name']);
            $firstName = trim(explode(' ', $fullName[1])[0]);
            $lastName  = trim($fullName[0]);
            $date      = Carbon\Carbon::parse(trim($n['dob']));
            $userObj   = CircleLinkHealth\Customer\Entities\User::ofPractice(201)->whereFirstName($firstName)->whereLastName(
                $lastName
            )->whereHas(
                'patientInfo',
                function ($q) use ($date) {
                    $q->whereBirthDate($date->toDateString());
                }
            )->first();
            if ($userObj) {
                if ($userObj->patientInfo->mrn_number == $n['mrn']) {
                    $sameMrn[] = $n;
                } else {
                    $matched[]                        = $n;
                    $userObj->patientInfo->mrn_number = $n['mrn'];
                    $userObj->patientInfo->save();
                }
            } else {
                $notFound[] = $n;
            }
        }

        file_put_contents(storage_path('nbi_mrn_not_found'), json_encode(['patients' => $notFound]));
        file_put_contents(storage_path('nbi_mrn_found'), json_encode(['patients' => $matched]));
        file_put_contents(storage_path('nbi_mrn_already_valid'), json_encode(['patients' => $sameMrn]));
    }
}
