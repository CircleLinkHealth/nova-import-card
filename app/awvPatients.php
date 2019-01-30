<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class awvPatients extends Model
{
    protected $fillable = [
        'cpm_user_id',
        'number',
    ];

    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'aw_patient_id');
    }
   /* public function user()
    {
        return $this->hasOne(User::class, 'cpm_user_id');
    }*/
}
