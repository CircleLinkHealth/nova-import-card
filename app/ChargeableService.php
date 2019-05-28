<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\ChargeableService.
 *
 * @property int                                                                                                  $id
 * @property string                                                                                               $code
 * @property string|null                                                                                          $description
 * @property float|null                                                                                           $amount
 * @property \Illuminate\Support\Carbon|null                                                                      $created_at
 * @property \Illuminate\Support\Carbon|null                                                                      $updated_at
 * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection              $practices
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                  $providers
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                       $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChargeableService extends \CircleLinkHealth\Core\Entities\BaseModel
{
    const AWV_INITIAL    = 'AWV: G0438';
    const AWV_SUBSEQUENT = 'AWV: G0439';
    /**
     * When a Patient consents to receive Care from CLH, they consent to these Chargeable Services, if consent date is
     * after 7/23/2018. If consent date is before 7/23/2018, patient was consented to the same services except for 'CPT
     * 99484'.
     */
    const DEFAULT_CHARGEABLE_SERVICE_CODES = [
        'CPT 99484',
        'CPT 99490',
        'CPT 99487',
        'CPT 99489',
        'G0511',
    ];

    protected $fillable = [
        'code',
        'description',
        'amount',
    ];

    public static function defaultServices()
    {
        return self::whereIn('code', self::DEFAULT_CHARGEABLE_SERVICE_CODES)->get();
    }

    public function patientSummaries()
    {
        return $this->morphedByMany(PatientMonthlySummary::class, 'chargeable')
            ->withTimestamps();
    }

    public function practices()
    {
        return $this->morphedByMany(Practice::class, 'chargeable')
            ->withTimestamps();
    }

    public function providers()
    {
        return $this->morphedByMany(User::class, 'chargeable')
            ->withTimestamps();
    }
}
