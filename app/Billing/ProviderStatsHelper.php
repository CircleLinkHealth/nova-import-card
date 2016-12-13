<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 12/12/16
 * Time: 2:40 PM
 */

namespace App\Billing;

use App\Activity;
use App\Call;
use App\MailLog;
use App\Note;
use App\Observation;
use App\User;
use Carbon\Carbon;

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

}