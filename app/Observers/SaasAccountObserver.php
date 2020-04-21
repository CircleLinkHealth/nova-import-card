<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Support\Str;

class SaasAccountObserver
{
    /**
     * Listen to the SaasAccount creating event.
     */
    public function creating(SaasAccount $saasAccount)
    {
        $saasAccount->slug = Str::slug($saasAccount->name);
    }
}
