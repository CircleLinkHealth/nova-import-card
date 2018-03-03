<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Chargeable;

class ChargeableService extends Model
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

    public function chargeables() {
        return $this->hasMany(Chargeable::class);
    }
}
