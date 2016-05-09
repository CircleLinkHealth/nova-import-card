<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmSmoking extends Model implements Biometric {

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }


    public function getUserValues(User $user)
    {
        $biometric = $this->wherePatientId($user->ID)->first();
        return [
            'starting' => $biometric->starting,
            'target' => $biometric->target
        ];
    }
}
