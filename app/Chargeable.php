<?php

namespace App;

use App\ChargeableService;
/**
 * App\Chargeable
 *
 * @property int $chargeable_service_id
 * @property int $chargeable_id
 * @property string $chargeable_type
 * @property $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\ChargeableService $chargeable_service
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereIsCcmComplex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereMonthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereNoOfCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereNoOfSuccessfulCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary wherePatientInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Chargeable extends \App\BaseModel
{

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

    public function chargeableService() {
        return $this->belongsTo(ChargeableService::class);
    }
}
