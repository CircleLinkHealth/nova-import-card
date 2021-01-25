<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;


use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientForcedChargeableService extends Pivot
{
    //todo: test this works on pivot model, i.e: do the same model events fire for pivot models?
    //create Pivot revisionable if necessary
    use RevisionableTrait;

    const FORCE_ACTION_TYPE = 'force';
    const BLOCK_ACTION_TYPE = 'block';

    protected $fillable = [
        'action_type',
        'chargeable_month',
        'chargeable_service_id',
        'patient_user_id'
    ];

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