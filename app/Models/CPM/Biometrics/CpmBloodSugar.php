<?php namespace App\Models\CPM\Biometrics;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBloodSugar extends Model
{

    protected $fillable = [
        'high_alert',
        'low_alert',
        'patient_id',
        'starting',
        'starting_a1c',
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
