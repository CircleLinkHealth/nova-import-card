<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AwvPatients extends Model
{
    protected $fillable = [
        'cpm_user_id',
        'number',
    ];

    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'awv_user_id');
    }
}
