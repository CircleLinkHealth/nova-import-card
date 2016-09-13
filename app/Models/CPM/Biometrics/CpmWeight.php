<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmWeight extends Model implements Biometric
{

    protected $fillable = [
        'monitor_changes_for_chf',
        'patient_id',
        'starting',
        'target',
    ];

    public static $rules = [
        'starting' => 'max:999|numeric',
        'target' => 'max:999|numeric',
    ];

    public static $messages = [
        'starting.max' => 'The Starting Weight may not be greater than 999.',
        'target.max' => 'The Target Weight may not be greater than 999.',
    ];

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
        
        return $biometric
            ? [
                'starting' => $biometric->starting,
                'target' => $biometric->target
            ] 
            : false;
    }
}
