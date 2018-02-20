<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class EhrKeychain extends Model
{
    protected $fillable = [
        'patient_id',
        'ehr_patient_id',
        'ehr_id',
        'ehr_practice_id',
        'ehr_department_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');

    }
}
