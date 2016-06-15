<?php

namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use App\Models\CCD\QAImportSummary;
use App\Models\CCD\Ccda;
use App\User;
use Carbon\Carbon;


trait ValidatesQAImportOutput
{
    public function validateQAImportOutput(Ccda $ccda, $output)
    {
        $demographics = CcdDemographicsLog::whereCcdaId($ccda->id)->first();

        $name = function () use ($demographics) {
            return empty($name = $demographics->first_name . ' ' . $demographics->last_name)
                ?: $name;
        };

        $provider = function () use ($output) {
            if (isset($output['provider'][0])) return $output['provider'][0]['display_name'];
        };

        $location = function () use ($output) {
            if (isset($output['location'][0])) return $output['location'][0]['name'];
        };

        $duplicateCheck = function () use ($demographics) {
            $date = (new Carbon($demographics->dob))->format('Y-m-d');

            $dup = User::with([
                'patientInfo' => function ($q) use ($demographics, $date) {
                    $q->where('birth_date', '=', $date);
                }])
                ->whereFirstName($demographics->first_name)
                ->whereLastName($demographics->last_name)
                ->first();

            return empty($dup) ? null : $dup->ID;
        };

        $phoneCheck = function () use ($demographics) {
            return ($demographics->cell_phone
                || $demographics->home_phone
                || $demographics->work_phone);
        };

        $counter = function ($index) use ($output) {
            return count($output[$index]);
        };

        $hasStreetAddress = function () use ($demographics) {
            return empty($demographics->street) ? false : true;
        };

        $hasCity = function () use ($demographics) {
            return empty($demographics->city) ? false : true;
        };

        $hasState = function () use ($demographics) {
            return empty($demographics->state) ? false : true;
        };

        $hasZip = function () use ($demographics) {
            return empty($demographics->zip) ? false : true;
        };


        $qaSummary = new QAImportSummary();
        $qaSummary->ccda_id = $ccda->id;
        $qaSummary->name = $name();
        $qaSummary->medications = $counter(3);
        $qaSummary->problems = $counter(1);
        $qaSummary->allergies = $counter(0);
        $qaSummary->provider = $provider();
        $qaSummary->location = $location();
        $qaSummary->duplicate_id = $duplicateCheck();
        $qaSummary->has_street_address = $hasStreetAddress();
        $qaSummary->has_zip = $hasZip();
        $qaSummary->has_city = $hasCity();
        $qaSummary->has_state = $hasState();
        $qaSummary->has_phone = $phoneCheck();

        $isFlagged = false;

        if ($qaSummary->medications == 0
            || $qaSummary->problems == 0
            || empty($qaSummary->location)
            || empty($qaSummary->provider)
            || empty($qaSummary->name)
            || empty($qaSummary->has_street_address)
            || empty($qaSummary->has_city)
            || empty($qaSummary->has_state)
            || empty($qaSummary->has_zip)
            || ! $qaSummary->has_phone
        ) $isFlagged = true;

        $qaSummary->flag = $isFlagged;
        $qaSummary->save();
        $qaSummary['ccda']['source'] = $ccda->source;
        $qaSummary['ccda']['created_at'] = $ccda->created_at;

        return $qaSummary;
    }

}