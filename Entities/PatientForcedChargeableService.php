<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;


use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientForcedChargeableService extends Pivot
{
    use RevisionableTrait;

    const FORCE_ACTION_TYPE = 'force';
    const BLOCK_ACTION_TYPE = 'block';

    protected $appends = [
        'action_type',
        'chargeable_month'
    ];

    protected $dates = [
        'chargeable_month',
        'created_at',
        'updated_at'
    ];

    //todo: add logic on attach here?
}