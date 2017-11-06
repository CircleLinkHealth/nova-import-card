<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PatientSession
 *
 * @property int $id
 * @property int $patient_id
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSession wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSession whereUserId($value)
 * @mixin \Eloquent
 */
class PatientSession extends \App\BaseModel
{
    public $fillable = [
        'user_id',
        'patient_id'
    ];
}
