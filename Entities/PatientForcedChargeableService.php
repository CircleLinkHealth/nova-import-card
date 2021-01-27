<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientForcedChargeableService extends BaseModel
{
    const BLOCK_ACTION_TYPE = 'block';

    const FORCE_ACTION_TYPE = 'force';

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

    public static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            //if permanent process all non closed months? Just so if they chose permanent to apply changes for the past month as well
            ForcePatientChargeableService::executeWithoutAttaching(
                (new ForceAttachInputDTO())->setActionType($item->action_type)
                ->setChargeableServiceId($item->chargeable_service_id)
                ->setPatientUserId($item->patient_user_id)
                ->setMonth($item->chargeable_month)
            );
        });

//        static::deleting(function ($item) {
//            dd($item);
//            ForcePatientChargeableService::executeWithoutAttaching(
//                (new ForceAttachInputDTO())->setActionType($item->action_type)
//                                           ->setChargeableServiceId($item->chargeable_service_id)
//                                           ->setPatientUserId($item->patient_user_id)
//                                           ->setMonth($item->chargeable_month)
//                ->setEntryCreatedAt($item->created_at)
//                ->setIsDetaching(true)
//            );
//        });
    }

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }

    public static function getOpposingActionType(string $actionType): string
    {
        if (! in_array($actionType, [
            self::FORCE_ACTION_TYPE,
            self::BLOCK_ACTION_TYPE
        ])){
            throw new \Exception("Invalid Patient Forced Chargeable Service Action Type: $actionType");
        }
        return $actionType === self::FORCE_ACTION_TYPE ? self::BLOCK_ACTION_TYPE : self::FORCE_ACTION_TYPE;
    }
}
