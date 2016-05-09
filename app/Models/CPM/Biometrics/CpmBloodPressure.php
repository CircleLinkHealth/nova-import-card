<?php namespace App\Models\CPM\Biometrics;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBloodPressure extends Model
{

    protected $fillable = [
        'diastolic_high_alert',
        'diastolic_low_alert',
        'patient_id',
        'starting',
        'systolic_high_alert',
        'systolic_low_alert',
        'target',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

}
