<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachBillingProvider extends BaseCcdaImportTask
{
    protected function import()
    {
        $providerId = $this->ccda->billing_provider_id ?? null;

        if ( ! $providerId) {
            return;
        }

        $args = [
            'member_user_id' => $providerId,
            'alert'          => true,
        ];

        // firstOrCreate intentional here. Never change to updateOrCreate.
        // We get billing provider lists from the practice, and sometimes it's wrong. Providers may then be uploaded by users, changed by nurses etc.
        // So we don't want to touch any change an admin/nurse/practice staff may have performed.
        $billing = CarePerson::firstOrCreate(
            [
                'type'    => CarePerson::BILLING_PROVIDER,
                'user_id' => $this->patient->id,
            ],
            $args
        );

        if ( ! $billing->member_user_id) {
            $billing->member_user_id = $args['member_user_id'];
        }

        if ( ! $billing->alert) {
            $billing->alert = $args['alert'];
        }

        if ($billing->isDirty()) {
            $billing->save();
        }
    }
}
