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

        $counter = function ($index) use ($output) {
            return count($output[$index]);
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

        $isFlagged = false;

        if ($qaSummary->medications == 0 || $qaSummary->problems == 0 || empty($qaSummary->location) || empty($qaSummary->provider) || empty($qaSummary->name)) $isFlagged = true;

        $qaSummary->flag = $isFlagged;
        $qaSummary->save();
        $qaSummary['ccda']['source'] = $ccda->source;
        $qaSummary['ccda']['created_at'] = $ccda->created_at;

        return $qaSummary;
    }

}