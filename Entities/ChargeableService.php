<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Processors\Patient\AWV1;
use CircleLinkHealth\CcmBilling\Processors\Patient\AWV2;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM40;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM60;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\Entities\ChargeableService.
 *
 * @property int                                                                                         $id
 * @property string                                                                                      $code
 * @property string|null                                                                                 $description
 * @property float|null                                                                                  $amount
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|PatientMonthlySummary[]                            $patientSummaries
 * @property \Illuminate\Database\Eloquent\Collection|Practice[]                                         $practices
 * @property \Illuminate\Database\Eloquent\Collection|User[]                                             $providers
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereAmount($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereCode($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereDescription($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableService whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $patient_summaries_count
 * @property int|null $practices_count
 * @property int|null $providers_count
 * @property int|null $revision_history_count
 * @property int|null $order
 * @property int      $is_enabled
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereIsEnabled($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereOrder($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvInitial()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvSubsequent()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService bhi()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService ccm()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService generalCareManagement()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService pcm()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService softwareOnly()
 */
class ChargeableService extends BaseModel
{
    const AWV_INITIAL    = 'AWV: G0438';
    const AWV_SUBSEQUENT = 'AWV: G0439';
    const BHI            = 'CPT 99484';
    const CCM            = 'CPT 99490';
    const CCM_PLUS_40    = 'G2058(>40mins)';
    const CCM_PLUS_60    = 'G2058(>60mins)';
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
    const GENERAL_CARE_MANAGEMENT = 'G0511';
    const PCM                     = 'G2065';
    const SOFTWARE_ONLY           = 'Software-Only';

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

    public function scopeAwvInitial($query)
    {
        return $query->where('code', self::AWV_INITIAL);
    }

    public function scopeAwvSubsequent($query)
    {
        return $query->where('code', self::AWV_SUBSEQUENT);
    }

    public function scopeBhi($query)
    {
        return $query->where('code', self::BHI);
    }

    public function scopeCcm($query)
    {
        return $query->where('code', self::CCM);
    }

    public function scopeGeneralCareManagement($query)
    {
        return $query->where('code', self::GENERAL_CARE_MANAGEMENT);
    }

    public function scopePcm($query)
    {
        return $query->where('code', self::PCM);
    }

    public function scopeSoftwareOnly($query)
    {
        return $query->where('code', self::SOFTWARE_ONLY);
    }
    
    public function processor() : PatientChargeableServiceProcessor
    {
        $class = $this->processorClassMap()[$this->code] ?? null;
        
        return $class ? new $class : null;
    }
    
    public function processorClassMap() : array
    {
        return [
            self::CCM => CCM::class,
            self::BHI => BHI::class,
            self::CCM_PLUS_40 => CCM40::class,
            self::CCM_PLUS_60 => CCM60::class,
            self::PCM => PCM::class,
            self::AWV_INITIAL => AWV1::class,
            self::AWV_SUBSEQUENT => AWV2::class
        ];
    }
}
