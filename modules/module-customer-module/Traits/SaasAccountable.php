<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\Customer\Entities\SaasAccount;

trait SaasAccountable
{
    public function isNotSaas()
    {
        return ! $this->isSaas();
    }

    public function isSaas()
    {
        return $this->saas_account_id > 1;
    }

    public function saasAccount()
    {
        return $this->belongsTo(SaasAccount::class);
    }

    public function saasAccountName()
    {
        $saasAccount = $this->saasAccount;

        if ($saasAccount) {
            return $saasAccount->name;
        }

        return 'CircleLink Health';
    }
}
