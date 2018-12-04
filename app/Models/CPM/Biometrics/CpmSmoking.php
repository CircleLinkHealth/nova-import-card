<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use App\Models\CPM\CpmBiometric;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\Biometrics\CpmSmoking
 *
 * @property int $id
 * @property int $patient_id
 * @property string $starting
 * @property string $target
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereStarting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmSmoking extends \App\BaseModel implements Biometric
{
    public static $rules = [
        'starting' => 'max:999|numeric',
        'target' => 'max:999|numeric',
    ];
    public static $messages = [
        'starting.max' => 'The Starting Blood Sugar may not be greater than 999.',
        'target.max' => 'The Target Blood Sugar may not be greater than 999.',
    ];
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
    
    public function biometric()
    {
        return CpmBiometric::where('name', 'LIKE', '%smoking%');
    }


    public function getUserValues(User $user)
    {
        $biometric = $this->wherePatientId($user->id)->first();
        return $biometric
            ? [
                'starting' => $biometric->starting,
                'target' => $biometric->target
            ]
            : false;
    }
}
