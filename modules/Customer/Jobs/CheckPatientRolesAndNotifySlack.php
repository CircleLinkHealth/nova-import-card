<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Jobs;


use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPatientRolesAndNotifySlack implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $patientIds = User::whereHas('patientInfo', fn($q) => $q->enrolled())
                            ->notOfType('participant')
                            ->pluck('id')
                            ->toArray();

        if (! empty($patientIds)){
            $string = implode(',', $patientIds);
            sendSlackMessage('#cpm_general_alerts', "The following patients do are enrolled and do not have participant role: \n $string");
        }
    }
}