<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\GeneratePatientCallDataCsv;

use App\Note;
use CircleLinkHealth\Core\Exports\FromArray;
use App\Services\PatientCallData;
use Carbon\Carbon;

class GeneratePatientCallDataCsvController
{
    public function handle($monthYear)
    {
        $date = Carbon::parse($monthYear);

        $rows = PatientCallData::get($date);

        $fileName = 'patient-call-data-'.$date->format('F_Y').'.xls';

        $headings = [
            'Patient ID',
            'CCM Time (mins)',
            'BHI Time (mins)',
            'Total Calls with Patient',
            'Successful Calls with Patient',
            'Nurse Name',
            'Practice',
        ];

        return (new FromArray($fileName, $rows, $headings))->download($fileName);
    }
}
