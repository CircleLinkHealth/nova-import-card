<?php

namespace App\Observers;

use App\SaasAccount;

class SaasAccountObserver
{
    /**
     * Listen to the SaasAccount creating event.
     *
     * @param SaasAccount $saasAccount
     *
     * @return void
     */
    public function creating(SaasAccount $saasAccount)
    {
        $saasAccount->slug = str_slug($saasAccount->name);
    }
}
