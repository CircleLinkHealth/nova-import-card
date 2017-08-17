<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 2:40 PM
 */

namespace App\Reports\Sales\Practice;

use App\Activity;
use App\Call;
use App\MailLog;
use App\Observation;
use App\Patient;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class PracticeStatsHelper
{
    private $practice;

    public function __construct(
        Practice $practice
    ) {
        $this->practice = $practice;
    }

    public function enrollmentCount(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();
        
        $patients = User::ofType('participant')
            ->where('program_id', '=', $this->practice->id)
            ->get();

        $data = [
            'withdrawn' => 0,
            'paused'    => 0,
            'added'     => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->created_at->gte($start) && $patient->created_at->lte($end)) {
                $data['added']++;
            }

            if (!$patient->patientInfo) {
                continue;
            }

            if ($patient->patientInfo->date_withdrawn && $patient->patientInfo->date_withdrawn->gte($start) && $patient->patientInfo->date_withdrawn->lte($end)) {
                $data['withdrawn']++;
            }

            if ($patient->patientInfo->date_paused && $patient->patientInfo->date_paused->gte($start) && $patient->patientInfo->date_paused->lte($end)) {
                $data['paused']++;
            }
        }

        return $data;

    }

    public function successfulCallCount(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        return $this->callCount($start, $end, 'reached');
    }

    public function callCount(Carbon $start, Carbon $end, $status = null)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        $q = Call::whereHas('inboundUser', function ($q) {
                $q->where('program_id', '=', $this->practice->id);
            })
            ->where('called_date', '>=', $start)
            ->where('called_date', '<=', $end);

        if ($status) {
            $q->whereStatus($status);
        }

        return $q->count();
    }

    public function totalCCMTimeHours(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        $duration = Activity
            ::whereHas('patient', function ($q) {
                $q->where('program_id', '=', $this->practice->id);
            })
            ->where('performed_at', '>=', $start->toDateTimeString())
            ->where('performed_at', '<=', $end->toDateTimeString())
            ->sum('duration');

        return round($duration / 3600, 1);
    }

    public function numberOfBiometricsRecorded(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        return Observation::whereHas('user', function ($q) {
            $q->whereProgramId($this->practice->id);
        })
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();
    }

    public function noteStats(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        $providers = User::where('program_id', $this->practice->id)
            ->whereHas('roles', function ($q) {
                $q->whereName('provider');
            })->pluck('id')->toArray();

        return MailLog::whereIn('receiver_cpm_id', $providers)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->whereType('note')
            ->count();
    }

    public function emergencyNotesCount(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        return MailLog
            ::whereHas('note', function ($q) {
                $q->where('isTCM', 1)
                    ->whereHas('patient', function ($k) {
                        $k->where('program_id', $this->practice->id);
                    });
            })
            ->whereType('note')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();

    }

    public function linkToPracticeNotes()
    {
        return URL::route('patient.note.listing');
    }

    public function historicalEnrollmentPerformance(Carbon $start, Carbon $end)
    {
        $start = $start->startOfDay();
        $end = $end->endOfDay();

        $patients = User::ofType('participant')
            ->whereProgramId($this->practice->id)
            ->get();

        for ($i = 0; $i < 5; $i++) {
            if ($i == 0) {
                $start = $start;
                $end = $end;
            } else {
                $start = $start->copy()->subMonth($i)->firstOfMonth()->startOfDay();
                $end = $start->copy()->endOfMonth()->endOfDay();
            }

            $index = $start->toDateString();
            $data['withdrawn'][$index] = 0;
            $data['paused'][$index] = 0;
            $data['added'][$index] = 0;

            foreach ($patients as $patient) {
                if ($patient->created_at->gte($start) && $patient->created_at->lte($end)) {
                    $data['added'][$index]++;
                }

                if (!$patient->patientInfo) {
                    continue;
                }

                if ($patient->patientInfo->date_withdrawn && $patient->patientInfo->date_withdrawn->gte($start) && $patient->patientInfo->date_withdrawn->lte($end)) {
                    $data['withdrawn'][$index]++;
                }

                if ($patient->patientInfo->date_paused && $patient->patientInfo->date_paused->gte($start) && $patient->patientInfo->date_paused->lte($end)) {
                    $data['paused'][$index]++;
                }
            }
        }

        return $data;

    }

    public function totalBilled()
    {
        return PatientMonthlySummary::whereHas('patient_info', function ($q) {
            $q->whereHas('user', function ($k) {
                $k->whereProgramId($this->practice->id);
            });
        })
            ->where('ccm_time', '>', 1199)
            ->count();

    }

    public function billableCountForMonth(Carbon $month)
    {
        return PatientMonthlySummary::whereHas('patient_info', function ($q) {
            $q->whereHas('user', function ($k) {
                $k->whereProgramId($this->practice->id);
            });
        })
            ->where('ccm_time', '>', 1199)
            ->where('month_year', $month->firstOfMonth())
            ->count();

    }

    public function billableCountCurrentMonth()
    {
        $patientsForPractice = $this->practice->users()->ofType('participant')->get();

        $month = Carbon::now()->startOfMonth()->toDateString();
        $count = 0;

        foreach ($patientsForPractice as $patient) {

            $data[] = Patient::where('cur_month_activity_time', '>', 1199)
                ->whereUserId($patient->id)
                ->first();

            if ($data) {
                $count++;
            }

        }

        return $data;

    }

}