<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;


use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientForcedChargeableService extends Pivot
{
    protected $table = 'patient_forced_chargeable_services';
    //todo: test this works on pivot model, i.e: do the same model events fire for pivot models?
    //create Pivot revisionable if necessary
    use RevisionableTrait;

    public static function boot()
    {
        parent::boot();

//        static::saving(function ($item)  {
//            // this will die and dump on the first element passed to ->sync()
//            dd($item);
//        });
    }

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

    public function patient(){
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }

    public function chargeableService(){
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id', 'id');
    }

    //todo: Hack for Laravel\Nova\Http\Controllers\ResourceAttachController@handle
    public function attributesToArray()
    {
        return $this->getAttributes();
    }
}