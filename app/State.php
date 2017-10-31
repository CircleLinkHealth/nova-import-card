<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public $timestamps = false;

    public function nurses()
    {
        
        return $this->belongsToMany('App\NurseInfo');
    }
}
