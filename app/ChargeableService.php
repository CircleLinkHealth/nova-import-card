<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class ChargeableService extends \App\BaseModel
{
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
