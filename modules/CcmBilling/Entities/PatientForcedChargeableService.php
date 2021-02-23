<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property int                                                                                         $chargeable_service_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property string                                                                                      $action_type
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property ChargeableService                                                                           $chargeableService
 * @property User                                                                                        $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientForcedChargeableService newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientForcedChargeableService newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientForcedChargeableService query()
 * @mixin \Eloquent
 * @property string|null $reason
 */
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
        'reason',
    ];
    protected $table = 'patient_forced_chargeable_services';

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id', 'id');
    }

    public static function getOpposingActionType(string $actionType): string
    {
        if ( ! in_array($actionType, [
            self::FORCE_ACTION_TYPE,
            self::BLOCK_ACTION_TYPE,
        ])) {
            throw new \Exception("Invalid Patient Forced Chargeable Service Action Type: $actionType");
        }

        return self::FORCE_ACTION_TYPE === $actionType ? self::BLOCK_ACTION_TYPE : self::FORCE_ACTION_TYPE;
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id', 'id');
    }
}
