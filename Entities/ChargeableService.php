<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Processors\Patient\AWV1;
use CircleLinkHealth\CcmBilling\Processors\Patient\AWV2;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM40;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM60;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\RHC;
use CircleLinkHealth\CcmBilling\Processors\Patient\RPM;
use CircleLinkHealth\CcmBilling\Processors\Patient\RPM40;
use CircleLinkHealth\CcmBilling\Processors\Patient\RPM60;
use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
 * @property int|null    $patient_summaries_count
 * @property int|null    $practices_count
 * @property int|null    $providers_count
 * @property int|null    $revision_history_count
 * @property int|null    $order
 * @property int         $is_enabled
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereIsEnabled($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService whereOrder($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvInitial()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService awvSubsequent()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService bhi()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService ccm()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService generalCareManagement()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService pcm()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ChargeableService softwareOnly()
 * @property string|null $display_name
 */
class ChargeableService extends BaseModel
{
    const AWV_INITIAL    = 'AWV: G0438';
    const AWV_SUBSEQUENT = 'AWV: G0439';
    const BHI            = 'CPT 99484';
    const CCM            = 'CPT 99490';

    const CCM_CODES = [
        self::CCM,
        self::CCM_PLUS_40,
        self::CCM_PLUS_60,
    ];
    const CCM_PLUS_40 = 'CPT 99439(>40mins)';
    const CCM_PLUS_60 = 'CPT 99439(>60mins)';

    const CCM_PLUS_CODES = [
        self::CCM_PLUS_40,
        self::CCM_PLUS_60,
    ];

    const CLASHES = [
        self::PCM => [
            self::GENERAL_CARE_MANAGEMENT,
            self::CCM,
            self::CCM_PLUS_40,
            self::CCM_PLUS_60,
            self::RPM,
            self::RPM40,
        ],
        self::RPM => [
            self::GENERAL_CARE_MANAGEMENT,
        ],
        self::RPM40 => [
            self::GENERAL_CARE_MANAGEMENT,
        ],
        self::RPM60 => [
            self::GENERAL_CARE_MANAGEMENT,
        ],
    ];

    const CODES_THAT_CAN_HAVE_PROBLEMS = [
        self::CCM,
        self::BHI,
        self::PCM,
        self::RPM,
    ];

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
        self::GENERAL_CARE_MANAGEMENT,
    ];

    const FRIENDLY_NAMES = [
        self::CCM                     => 'CCM',
        self::CCM_PLUS_40             => 'CCM40',
        self::CCM_PLUS_60             => 'CCM60',
        self::BHI                     => 'BHI',
        self::GENERAL_CARE_MANAGEMENT => 'RHC',
        self::AWV_INITIAL             => 'AWV1',
        self::AWV_SUBSEQUENT          => 'AWV2+',
        self::PCM                     => 'PCM',
        self::RPM                     => 'RPM',
        self::RPM40                   => 'RPM40',
        self::RPM60                   => 'RPM60',
    ];

    const GENERAL_CARE_MANAGEMENT = 'G0511';

    const ONLY_PLUS_CODES = [
        self::CCM_PLUS_40,
        self::CCM_PLUS_60,
        self::RPM40,
        self::RPM60,
    ];

    const PCM = 'G2065';

    const REQUIRED_TIME_PER_SERVICE = [
        self::CCM                     => (20 * 60),
        self::CCM_PLUS_40             => (40 * 60),
        self::CCM_PLUS_60             => (60 * 60),
        self::PCM                     => (30 * 60),
        self::RPM                     => (20 * 60),
        self::RPM40                   => (40 * 60),
        self::RPM60                   => (60 * 60),
        self::GENERAL_CARE_MANAGEMENT => (20 * 60),
        self::BHI                     => (20 * 60),
    ];
    const RPM   = 'CPT 99457';
    const RPM40 = 'CPT 99458(>40mins)';
    const RPM60 = 'CPT 99458(>60mins)';

    const RPM_CODES = [
        self::RPM,
        self::RPM40,
        self::RPM60,
    ];

    const RPM_PLUS_CODES = [
        self::RPM40,
        self::RPM60,
    ];
    const SOFTWARE_ONLY = 'Software-Only';

    protected $fillable = [
        'order',
        'code',
        'display_name',
        'description',
        'amount',
    ];

    private static ?Collection $cached = null;

    public static function cached()
    {
        if ( ! self::$cached) {
            self::$cached = ChargeableService::all();
        }

        return self::$cached;
    }

    public static function defaultServices()
    {
        return self::whereIn('code', self::DEFAULT_CHARGEABLE_SERVICE_CODES)->get();
    }

    public function forcedForPatients()
    {
        return $this->belongsToMany(User::class, 'patient_forced_chargeable_services', 'chargeable_service_id', 'patient_user_id')
            ->withPivot([
                'is_forced',
                'chargeable_month',
            ])->withTimestamps();
    }

    public static function getChargeableServiceIdUsingCode(string $code): int
    {
        return Cache::remember("name:chargeable_service_$code", 2, function () use ($code) {
            return ChargeableService::where('code', $code)
                ->value('id');
        });
    }

    public static function getClashesWithService(string $service): array
    {
        if (self::GENERAL_CARE_MANAGEMENT === $service) {
            return [];
        }

        return self::CLASHES[$service] ?? [
            self::GENERAL_CARE_MANAGEMENT,
            self::RPM,
            self::RPM40,
            self::RPM60,
        ];
    }

    public static function getCodeForPatientProblems(string $code): string
    {
        //todo: cleaner mapping
        if (in_array($code, self::CCM_PLUS_CODES)) {
            return self::CCM;
        }

        if (self::GENERAL_CARE_MANAGEMENT === $code) {
            return self::CCM;
        }

        if (in_array($code, self::RPM_PLUS_CODES)) {
            return self::RPM;
        }

        return $code;
    }

    public static function getFriendlyName($code)
    {
        return self::FRIENDLY_NAMES[$code] ?? $code;
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

    public function processor(): ?PatientServiceProcessor
    {
        return $this->processorClassMap()[$this->code] ?? null;
    }

    public function processorClassMap(): array
    {
        return [
            self::CCM                     => new CCM(),
            self::BHI                     => new BHI(),
            self::CCM_PLUS_40             => new CCM40(),
            self::CCM_PLUS_60             => new CCM60(),
            self::PCM                     => new PCM(),
            self::AWV_INITIAL             => new AWV1(),
            self::AWV_SUBSEQUENT          => new AWV2(),
            self::GENERAL_CARE_MANAGEMENT => new RHC(),
            self::RPM                     => new RPM(),
            self::RPM40                   => new RPM40(),
            self::RPM60                   => new RPM60(),
        ];
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
}
