<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 2:40 PM
 */

namespace App\Reports\Sales\Provider;

use App\Activity;
use App\Call;
use App\CarePerson;
use App\MailLog;
use App\Observation;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class ProviderStatsHelper
{
    private $provider;
    private $start;
    private $end;

    public function __construct(
        User $provider,
        Carbon $st,
        Carbon $end
    ) {
        $this->provider = $provider;
        $this->start = $st;
        $this->end = $end;
    }

    public function successfulCallCount()
    {
        return $this->callCount('reached');
    }

    public function callCount($status = null)
    {
        $q = Call
            ::whereHas('inboundUser', function ($q) {
                $q->hasBillingProvider($this->provider->id);
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
                $q->hasBillingProvider($this->provider->id);
            })
            ->where('created_at', '>=', $this->start->toDateTimeString())
            ->where('created_at', '<=', $this->end->toDateTimeString())
            ->sum('duration');

        return round($duration / 3600, 1);

    }

    public function numberOfBiometricsRecorded()
    {
        return Observation
            ::whereHas('user', function ($q) {
                $q->hasBillingProvider($this->provider->id);
            })
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->end)
            ->count();
    }

    public function noteStats()
    {
        return MailLog
            ::whereReceiverCpmId($this->provider->id)
            ->whereNotNull('note_id')
            ->where('created_at', '>', $this->start->toDateTimeString())
            ->where('created_at', '<', $this->end->toDateTimeString())
            ->count();
    }

    public function emergencyNotesCount()
    {
        return MailLog
            ::whereHas('note', function ($q) {
                $q->where('isTCM', 1);
            })
            ->whereReceiverCpmId($this->provider->id)
            ->whereNotNull('note_id')
            ->where('created_at', '>', $this->start)
            ->where('created_at', '<', $this->end)
            ->count();
    }

    public function linkToProviderNotes()
    {
        return URL::route('patient.note.listing') . "/?provider=$this->provider->id";
    }

    public function enrollmentCount()
    {
        $patients = User::ofType('participant')
            ->whereHas('careTeamMembers', function ($q) {

                $q->whereType(CarePerson::BILLING_PROVIDER)
                    ->whereMemberUserId($this->provider->id);

            })->get();

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

    public function historicalEnrollmentPerformance()
    {
        $patients = User::ofType('participant')
            ->whereHas('careTeamMembers', function ($q) {
                $q->whereType(CarePerson::BILLING_PROVIDER)
                    ->whereMemberUserId($this->provider->id);
            })->get();

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
        return PatientMonthlySummary
            ::whereHas('patient_info', function ($q) {
                $q->whereHas('user', function ($k) {
                    $k->whereHas('careTeamMembers', function ($q) {
                        $q->whereType(CarePerson::BILLING_PROVIDER)
                            ->whereMemberUserId($this->provider->id);
                    });
                });
            })
            ->where('ccm_time', '>', 1199)
            ->count();
    }

    public function billableCountForMonth(Carbon $month) {
        return User::ofType('participant')
            ->whereHas('careTeamMembers', function ($q) {
                $q->whereType(CarePerson::BILLING_PROVIDER)
                    ->whereMemberUserId($this->provider->id);
            })->whereHas('patientInfo', function ($k) use (
                $month
            ) {
                $k->whereHas('patientSummaries', function ($j) use (
                    $month
                ) {
                    $j->where('month_year', $month->firstOfMonth())
                        ->where('ccm_time', '>', 1199);
                });
            })->count();
    }
}