<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales;

use CircleLinkHealth\CpmAdmin\Contracts\Reportable;
use Carbon\Carbon;

class StatsHelper
{
    protected $reportable;

    public function __construct(Reportable $reportable)
    {
        $this->reportable = $reportable;
    }

    public function billableCountForMonth(Carbon $month)
    {
        return $this->reportable->billablePatientsCountForMonth($month);
    }

    public function callCount(Carbon $start, Carbon $end, $status = null)
    {
        return $this->reportable->callCount($start->startOfDay(), $end->endOfDay(), $status);
    }

    public function emergencyNotesCount(Carbon $start, Carbon $end)
    {
        return $this->reportable->forwardedEmergencyNotesCount($start->startOfDay(), $end->endOfDay());
    }

    public function enrollmentCount(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end   = $end->endOfDay();

        $patients = $this->reportable->patients();

        $data = [
            'withdrawn' => 0,
            'paused'    => 0,
            'added'     => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->created_at->gte($start) && $patient->created_at->lte($end)) {
                ++$data['added'];
            }

            if ( ! $patient->patientInfo) {
                continue;
            }

            if ($patient->patientInfo->date_withdrawn && $patient->patientInfo->date_withdrawn->gte($start) && $patient->patientInfo->date_withdrawn->lte($end)) {
                ++$data['withdrawn'];
            }

            if ($patient->patientInfo->date_paused && $patient->patientInfo->date_paused->gte($start) && $patient->patientInfo->date_paused->lte($end)) {
                ++$data['paused'];
            }
        }

        return $data;
    }

    public function historicalEnrollmentPerformance(Carbon $start, Carbon $end)
    {
        $endMonthStart = $end->startOfDay();

        $patients = $this->reportable->patients();

        for ($i = 0; $i < 5; ++$i) {
            $start = $endMonthStart->copy()->subMonth($i)->firstOfMonth()->startOfDay();
            $end   = $start->copy()->endOfMonth()->endOfDay();

            $index = $start->toDateString();
//            $data['withdrawn'][$index] = 0;
//            $data['paused'][$index] = 0;
            $data['added'][$index] = 0;

            foreach ($patients as $patient) {
                if (optional($patient->created_at)->gte($start) && optional($patient->created_at)->lte($end)) {
                    ++$data['added'][$index];
                }

                if ( ! $patient->patientInfo) {
                    continue;
                }

//                if (!empty($patient->patientInfo->date_withdrawn) && $patient->patientInfo->date_withdrawn->gte($start) && $patient->patientInfo->date_withdrawn->lte($end)) {
//                    $data['withdrawn'][$index]++;
//                }
//
//                if (!empty($patient->patientInfo->date_paused) && $patient->patientInfo->date_paused->gte($start) && $patient->patientInfo->date_paused->lte($end)) {
//                    $data['paused'][$index]++;
//                }
            }
        }

        return $data;
    }

    public function linkToNotes()
    {
        return $this->reportable->linkToNotes();
    }

    public function noteStats(Carbon $start, Carbon $end)
    {
        return $this->reportable->forwardedNotesCount($start->startOfDay(), $end->endOfDay());
    }

    public function numberOfBiometricsRecorded(Carbon $start, Carbon $end)
    {
        return $this->reportable->observationsCount($start->startOfDay(), $end->endOfDay());
    }

    public function successfulCallCount(Carbon $start, Carbon $end)
    {
        return $this->callCount($start->startOfDay(), $end->endOfDay(), 'reached');
    }

    public function totalBilled()
    {
        return $this->reportable->totalBilledPatientsCount();
    }

    public function totalCCMTimeHours(Carbon $start, Carbon $end)
    {
        $duration = $this->reportable->activitiesDuration($start->startOfDay(), $end->endOfDay());

        return round($duration / 3600, 1);
    }
}
