<?php

namespace App\Models\CCD;

use App\Scopes\Universal\MedicalRecordIdAndTypeTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CcdInsurancePolicy extends Model
{
    use MedicalRecordIdAndTypeTrait,
        SoftDeletes;

    protected $fillable = [
        'ccda_id',
        'patient_id',
        'name', //required
        'type',
        'policy_id', //required
        'relation',
        'subscriber',
        'approved'
    ];
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
