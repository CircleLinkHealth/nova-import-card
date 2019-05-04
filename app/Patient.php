<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient  extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'user_id',
        'birth_date',
    ];

    protected $table = 'patient_info';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function url()
    {
        return $this->hasMany(InvitationLink::class, 'patient_info_id');
    }
}
