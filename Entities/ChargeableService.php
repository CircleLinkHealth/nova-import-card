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
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
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
 * @property-read int|null $patient_summaries_count
 * @property-read int|null $practices_count
 * @property-read int|null $providers_count
 * @property-read int|null $revision_history_count
 * @property int|null $order
 * @property int $is_enabled
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvInitial()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvSubsequent()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService bhi()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService ccm()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService generalCareManagement()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService pcm()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService softwareOnly()
 */
class ChargeableService extends BaseModel
{
    const BHI = 'CPT 99484';
    const CCM = 'CPT 99490';
    const CCM_PLUS_40 = 'G2058(>40mins)';
    const CCM_PLUS_60 = 'G2058(>60mins)';
    const GENERAL_CARE_MANAGEMENT = 'G0511';
    const SOFTWARE_ONLY = 'Software-Only';
    const PCM = 'G2065';

    const AWV_INITIAL = 'AWV: G0438';
    const AWV_SUBSEQUENT = 'AWV: G0439';
    /**
     * When a Patient consents to receive Care from CLH, they consent to these Chargeable Services, if consent date is
     * after 7/23/2018. If consent date is before 7/23/2018, patient was consented to the same services except for 'CPT
     * 99484'.
     */
    const DEFAULT_CHARGEABLE_SERVICE_CODES = [
        self::BHI,
        self::CCM,
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

    public function scopePcm($query){
        return $query->where('code', self::PCM);
    }

    public function scopeCcm($query){
        return $query->where('code', self::CCM);
    }

    public function scopeBhi($query){
        return $query->where('code', self::BHI);
    }

    public function scopeAwvInitial($query){
        return $query->where('code', self::AWV_INITIAL);
    }

    public function scopeAwvSubsequent($query){
        return $query->where('code', self::AWV_SUBSEQUENT);
    }

    public function scopeGeneralCareManagement($query){
        return $query->where('code', self::GENERAL_CARE_MANAGEMENT);
    }

    public function scopeSoftwareOnly($query){
        return $query->where('code', self::SOFTWARE_ONLY);
    }
}

