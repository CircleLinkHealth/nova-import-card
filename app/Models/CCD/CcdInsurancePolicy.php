<?php

namespace App\Models\CCD;

use App\Scopes\Universal\MedicalRecordIdAndTypeTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CcdInsurancePolicy extends \App\BaseModel
{
    use MedicalRecordIdAndTypeTrait,
        SoftDeletes;

    protected $fillable = [
        'medical_record_id',
        'medical_record_type',
        'patient_id',
        'name', //required
        'type',
        'policy_id', //required
        'relation',
        'subscriber',
        'approved',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
