<?php

namespace App;

class ChargeableService extends \App\BaseModel
{
    const DEFAULT_CHARGEABLE_SERVICE_CODES = [
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

    public function patientSummaries()
    {
        return $this->morphedByMany(PatientMonthlySummary::class, 'chargeable')
                    ->withTimestamps();
    }
}
