<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 02/03/2018
 * Time: 5:08 PM
 */

namespace App\Traits;

use App\SaasAccount;

trait SaasAccountable
{
    public function saasAccount()
    {
        return $this->belongsTo(SaasAccount::class);
    }

    public function isSaas()
    {
        return $this->saas_account_id > 1;
    }

    public function isNotSaas()
    {
        return !$this->isSaas();
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
