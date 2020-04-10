<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class ImportBillingProvider extends BaseCcdaImportTask
{
    protected function import()
    {
        $providerId = $this->ccda->billing_provider_id ?? null;
    
        if (! $providerId) {
            return;
        }
    
        $args = [
            'member_user_id' => $providerId,
            'alert'          => true,
        ];
    
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