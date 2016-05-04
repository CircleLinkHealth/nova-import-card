<?php namespace App\Models\CPM\Biometrics;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBloodPressure extends Model {

	protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

}
