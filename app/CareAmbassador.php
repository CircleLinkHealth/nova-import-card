<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareAmbassador extends Model
{

    protected $fillable = [
        'user_id',
        'hourly_rate'
    ];

    public function user(){

        return $this->belongsTo(User::class, 'user_id');

    }

}
