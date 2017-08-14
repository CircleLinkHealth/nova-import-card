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
    private $start;
    private $end;

    public function __construct(
        Practice $practice,
        Carbon $st,
        Carbon $end
    ) {
        $this->practice = $practice;
        $this->start = $st->startOfDay();
        $this->end = $end->endOfDay();
    }

    public function enrollmentCount()
    {
        $patients = User
            ::ofType('participant')
            ->whereProgramId($this->practice->id)
            ->get();

        $data = [
            'withdrawn' => 0,
            'paused'    => 0,
            'added'     => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->created_at->gte($this->start) && $patient->created_at->lte($this->end)) {
                $data['added']++;
            }

            if (!$patient->patientInfo) {
                continue;
            }

            if ($patient->patientInfo->date_withdrawn && $patient->patientInfo->date_withdrawn->gte($this->start) && $patient->patientInfo->date_withdrawn->lte($this->end)) {
                $data['withdrawn']++;
            }

            if ($patient->patientInfo->date_paused && $patient->patientInfo->date_paused->gte($this->start) && $patient->patientInfo->date_paused->lte($this->end)) {
                $data['paused']++;
            }
        }

        return $data;

    }

    public function successfulCallCount()
    {
        return $this->callCount('reached');
    }

    public function callCount($status = null)
    {
        $q = Call
            ::whereHas('inboundUser', function ($q) {
                $q->whereProgramId($this->practice->id);
            })
            ->where('called_date', '>=', $this->start)
            ->where('called_date', '<=', $this->end);

        if ($status) {
            $q->whereStatus($status);
        }

        return $q->count();
    }

    public function totalCCMTimeHours()
    {
        $duration = Activity
            ::whereHas('patient', function ($q) {
                $q->whereProgramId($this->practice->id);
            })
            ->where('performed_at', '>=', $this->start->toDateTimeString())
            ->where('performed_at', '<=', $this->end->toDateTimeString())
            ->sum('duration');

        return round($duration / 3600, 1);
    }

    public function numberOfBiometricsRecorded()
    {
        return Observation::whereHas('user', function ($q) {
            $q->whereProgramId($this->practice->id);
        })
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->end)
            ->count();
    }

    public function noteStats()
    {
        $providers = User::where('program_id', $this->practice->id)
            ->whereHas('roles', function ($q) {
                $q->whereName('provider');
            })->pluck('id')->toArray();

        return MailLog::whereIn('receiver_cpm_id', $providers)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->end)
            ->whereType('note')
            ->count();
    }

    public function emergencyNotesCount()
    {
        return MailLog
            ::whereHas('note', function ($q) {
                $q->where('isTCM', 1)
                    ->whereHas('patient', function ($k) {
                        $k->where('program_id', $this->practice->id);
                    });
            })
            ->whereType('note')
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->end)
            ->count();

    }

    public function linkToPracticeNotes()
    {
        return URL::route('patient.note.listing'); //. "/?provider=$provider->id";
    }

    public function historicalEnrollmentPerformance()
    {
        $patients = User::ofType('participant')
            ->whereProgramId($this->practice->id)
            ->get();

        for ($i = 0; $i < 5; $i++) {
            if ($i == 0) {
                $start = $this->start;
                $end = $this->end;
            } else {
                $start = $this->start->copy()->subMonth($i)->firstOfMonth()->startOfDay();
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

            $data[] = Patient
                ::where('cur_month_activity_time', '>', 1199)
                ->whereUserId($patient->id)
                ->first();

            if ($data) {
                $count++;
            }

        }

        return $data;

    }

}