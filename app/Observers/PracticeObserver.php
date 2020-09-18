<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;

class PracticeObserver
{
    /**
     * Listen to the Practice created event.
     */
    public function created(Practice $practice)
    {
        User::where('saas_account_id', $practice->saas_account_id)
            ->where('auto_attach_programs', true)
            ->whereDoesntHave('practices', function ($q) use ($practice) {
                $q->where('practices.id', $practice->id);
            })
            ->each(function ($user) use ($practice) {
                if ($roleId = $user->practiceOrGlobalRole(true)) {
                    $user->attachRoleForSite($roleId, $practice->id);
                }
            }, 500);
    }

    /**
     * Listen to the Practice creating event.
     */
    public function creating(Practice $practice)
    {
        if ( ! $practice->saas_account_id && ! auth()->guest()) {
            $practice->saas_account_id = auth()->user()->saas_account_id;
        }
    }

    public function saved(Practice $practice)
    {
        if ($practice->isDirty('default_user_scope') && $practice->default_user_scope !== $practice->getOriginal('default_user_scope')) {
            User::ofPractice($practice)->ofType(CpmConstants::PRACTICE_STAFF_ROLE_NAMES)->update([
                'scope' => $practice->default_user_scope,
            ]);
            if (User::SCOPE_LOCATION === $practice->default_user_scope) {
                ProviderInfo::whereIn('user_id', function ($q) use ($practice) {
                    $q->select('user_id')
                        ->from('practice_role_user')
                        ->where('program_id', $practice->id)
                        ->whereIn('role_id', Role::getIdsFromNames(['provider']));
                })->update([
                    'approve_own_care_plans' => true,
                ]);
            }
        }
    }
}
