<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class awvPatients extends Model
{
    protected $fillable = [
        'cpm_user_id',
        'number',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'cpm_user_id');
    }
}
