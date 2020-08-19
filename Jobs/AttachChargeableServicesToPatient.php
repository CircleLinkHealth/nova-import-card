<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AttachChargeableServicesToPatient implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private User $patientUser;

    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
    }

    public function handle()
    {
        //using  patient-cs processors attach summaries at the start of month, or at the end of month fulfill
        //
        //and attach services that have not been accounted for
        
        //use past summaries at all to determine unfulfilled at the start of month?

        //get practice/location available codes to narrow down chargeable services to process, or check all for patient then enable/disable depending on Practice CS.
    }
}
