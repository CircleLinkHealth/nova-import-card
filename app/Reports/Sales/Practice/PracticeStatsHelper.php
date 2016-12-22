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
use App\PatientCareTeamMember;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class PracticeStatsHelper
{

    private $start;
    private $end;

    public function __construct(Carbon $st, Carbon $end)
    {

        $this->start = $st;
        $this->end = $end;

    }

    public function callCountForPractice(Practice $practice){

        $id = $practice->id;

        return Call
            ::whereHas('inboundUser',function ($q) use ($id){
                $q->whereProgramId($id);
            })
            ->where('called_date', '>', $this->start)
            ->where('called_date', '<', $this->end)
            ->count();

    }

    public function successfulCallCountForPractice(Practice $practice){

        $id = $practice->id;

        return Call
            ::whereHas('inboundUser',function ($q) use ($id){
                $q->whereProgramId($id);
            })
            ->where('called_date', '>', $this->start)
            ->where('called_date', '<', $this->end)
            ->whereStatus('reached')
            ->count();

    }

    public function totalCCMTime(Practice $practice){

        $id = $practice->id;

        return gmdate('H:i', Activity
            ::whereHas('patient',function ($q) use ($id){
                $q->whereProgramId($id);
            })
            ->where('created_at', '>', $this->start)
            ->where('created_at', '<', $this->end)
            ->count('duration'));

    }

    public function numberOfBiometricsRecorded(Practice $practice){

        $id = $practice->id;

        return Observation
            ::whereHas('user',function ($q) use ($id){
                $q->whereProgramId($id);
            })
            ->where('created_at', '>', $this->start)
            ->where('created_at', '<', $this->end)
            ->count();

    }

    public function noteStats(Practice $practice){

        $id = $practice->id;

        return MailLog
            ::whereHas('note',function ($q) use ($id){
                $q->whereHas('patient', function ($k) use ($id){
                    $k->whereProgramId($id);
                });
            })
        ->whereNotNull('note_id')
        ->count();

    }

    public function emergencyNotesCount(Practice $practice){

        $id = $practice->id;

        return MailLog
            ::whereHas('note', function ($q) use ($id){
                $q->where('isTCM', 1)
                  ->whereNotNull('note_id')
                    ->whereHas('patient', function ($k) use ($id){
                    $k->whereProgramId($id);

                });
            })->count();

    }

    public function linkToPracticeNotes(Practice $practice){

        return URL::route('patient.note.listing'); //. "/?provider=$provider->id";

    }

    public function enrollmentCountByPractice(Practice $practice, Carbon $start, Carbon $end){

        $patients = User
                    ::ofType('participant')
                    ->whereProgramId($practice->id)
                    ->get();

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

    public function totalBilled(Practice $practice){

        return PatientMonthlySummary
            ::whereHas('patient_info', function ($q) use ($practice){
                $q->whereHas('user', function ($k) use ($practice){
                    $k->whereProgramId($practice->id);
            });
        })
            ->where('ccm_time', '>', 1199)
            ->count();

    }

    public function billableCountForMonth(Practice $practice, Carbon $month){

        return User::ofType('participant')

            ->whereProgramId($practice->id)
            ->whereHas('patientInfo', function ($k) use ($month){

                $k->whereHas('patientSummaries', function ($j) use ($month){

                  $j->where('month_year', $month->firstOfMonth())
                    ->where('ccm_time', '>', 1199);

                });
            })
            ->count();

    }



}