<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\Entities\ChargeableService.
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property float|null $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
 * @property Practice[]|\Illuminate\Database\Eloquent\Collection $practices
 * @property User[]|\Illuminate\Database\Eloquent\Collection $providers
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeableService whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChargeableService extends BaseModel
{
    const AWV_INITIAL = 'AWV: G0438';
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
