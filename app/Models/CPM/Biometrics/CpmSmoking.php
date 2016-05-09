<?php namespace App\Models\CPM\Biometrics;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmSmoking extends Model
{

    protected $fillable = [
        'patient_id',
        'starting',
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
