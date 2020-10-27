<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;


use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\Customer\Entities\User;

class LegacyBillableCcdProblemsView extends SqlViewModel
{
    protected $table = 'legacy_billable_ccd_problems';
    
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}