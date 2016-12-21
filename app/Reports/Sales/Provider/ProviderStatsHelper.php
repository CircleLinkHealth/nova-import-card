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
use App\MailLog;
use App\Observation;
use App\PatientCareTeamMember;
use App\PatientInfo;
use App\PatientMonthlySummary;
use App\Practice;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class ProviderStatsHelper
{

    private $start;
    private $end;

    public function __construct(Carbon $st, Carbon $end)
    {

        $this->start = $st;
        $this->end = $end;

    }

    public function callCountForProvider(User $provider){

        $id = $provider->id;

        return Call
            ::whereHas('inboundUser',function ($q) use ($id){
                $q->hasBillingProvider($id);
            })
            ->where('called_date', '>', $this->start)
            ->where('called_date', '<', $this->end)
            ->count();

    }

    public function successfulCallCountForProvider(User $provider){

        $id = $provider->id;

        return Call
            ::whereHas('inboundUser',function ($q) use ($id){
                $q->hasBillingProvider($id);
            })
            ->where('called_date', '>', $this->start)
            ->where('called_date', '<', $this->end)
            ->whereStatus('reached')
            ->count();

    }

    public function totalCCMTime(User $provider){

        $id = $provider->id;

        return gmdate('H:i', Activity
            ::whereHas('patient',function ($q) use ($id){
            $q->hasBillingProvider($id);
            })
            ->where('created_at', '>', $this->start)
            ->where('created_at', '<', $this->end)
            ->count('duration'));

    }

    public function numberOfBiometricsRecorded(User $provider){

        $id = $provider->id;

        return Observation
            ::whereHas('user',function ($q) use ($id){
                $q->hasBillingProvider($id);
            })
            ->where('created_at', '>', $this->start)
            ->where('created_at', '<', $this->end)
            ->count();

    }

    public function noteStats(User $provider){

        $id = $provider->id;

        return MailLog
        ::whereReceiverCpmId($id)
        ->whereNotNull('note_id')
        ->count();

    }

    public function emergencyNotesCount(User $provider){

        $id = $provider->id;

        return MailLog
            ::whereHas('note', function ($q){
                $q->where('isTCM', 1);
            })
        ->whereReceiverCpmId($id)
        ->whereNotNull('note_id')
        ->count();

    }

    public function linkToProviderNotes(User $provider){

        return URL::route('patient.note.listing') . "/?provider=$provider->id";

    }

    public function enrollmentCountByProvider(User $billingProvider, Carbon $start, Carbon $end){

        $patients = User::ofType('participant')
            ->whereHas('patientCareTeamMembers', function ($q) use ($billingProvider){

            $q->whereType(PatientCareTeamMember::BILLING_PROVIDER)
              ->whereMemberUserId($billingProvider->id);

        })->get();

        $data = [

            'withdrawn' => 0,
            'paused' => 0,
            'added' => 0,

        ];

        foreach ($patients as $patient){

            if($patient->created_at > $start->toDateTimeString() && $patient->created_at <= $end->toDateTimeString()){

                $data['added']++;

            }

            if($patient->patientInfo->date_withdrawn > $start->toDateTimeString() && $patient->patientInfo->date_withdrawn <= $end->toDateTimeString()){

                $data['withdrawn']++;

            }

            if($patient->patientInfo->date_paused > $start->toDateTimeString() && $patient->patientInfo->date_paused <= $end->toDateTimeString()){

                $data['paused']++;

            }

        }

        return $data;

    }

    public function totalBilled(User $billingProvider){

        return PatientMonthlySummary
            ::whereHas('patient_info', function ($q) use ($billingProvider){
                $q->whereHas('user', function ($k) use ($billingProvider){
                    $k->whereHas('patientCareTeamMembers', function ($q) use ($billingProvider){
                        $q->whereType(PatientCareTeamMember::BILLING_PROVIDER)
                          ->whereMemberUserId($billingProvider->id);

                    });
            });
        })
            ->where('ccm_time', '>', 1199)
            ->count();

    }

    public function billableCountForMonth(User $billingProvider, Carbon $month){

        return User::ofType('participant')

            ->whereHas('patientCareTeamMembers', function ($q) use ($billingProvider){

                $q->whereType(PatientCareTeamMember::BILLING_PROVIDER)
                  ->whereMemberUserId($billingProvider->id);

            })->whereHas('patientInfo', function ($k) use ($billingProvider, $month){

                $k->whereHas('patientSummaries', function ($j) use ($month){

                  $j->where('month_year', $month->firstOfMonth())
                    ->where('ccm_time', '>', 1199);

                });
            })
            ->count();

    }



}