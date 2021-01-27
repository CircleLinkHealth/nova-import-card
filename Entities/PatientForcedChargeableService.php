<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientForcedChargeableService extends Pivot
{
    //todo: test this works on pivot model, i.e: do the same model events fire for pivot models?
    //create Pivot revisionable if necessary
    use RevisionableTrait;
    const BLOCK_ACTION_TYPE = 'block';

    const FORCE_ACTION_TYPE = 'force';

    protected $appends = [
        'action_type',
        'chargeable_month',
    ];

    protected $dates = [
        'chargeable_month',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'action_type',
        'chargeable_month',
        'chargeable_service_id',
        'patient_user_id',
    ];
    protected $table = 'patient_forced_chargeable_services';

    //todo: Hack for Laravel\Nova\Http\Controllers\ResourceAttachController@handle
    public function attributesToArray()
    {
        return $this->getAttributes();
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            //if permanent process all non closed months? Just so if they chose permanent to apply changes for the past month as well
            ForcePatientChargeableService::onPivotModelEvent($item->patient_user_id, $item->chargeable_service_id, $item->action_type, $item->chargeable_month);
        });

        static::deleted(function ($item) {
            ForcePatientChargeableService::onPivotModelEvent($item->patient_user_id, $item->chargeable_service_id, $item->action_type, $item->chargeable_month);
        });
    }

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }
}
