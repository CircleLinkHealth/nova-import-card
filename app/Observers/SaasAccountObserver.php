<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use Illuminate\Support\Str;
use CircleLinkHealth\Customer\Entities\SaasAccount;

class SaasAccountObserver
{
    /**
     * Listen to the SaasAccount creating event.
     *
     * @param SaasAccount $saasAccount
     */
    public function creating(SaasAccount $saasAccount)
    {
        $saasAccount->slug = Str::slug($saasAccount->name);
    }
}
