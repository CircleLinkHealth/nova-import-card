<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends \App\BaseModel
{
    public $timestamps = false;

    public function nurses()
    {
        
        return $this->belongsToMany('App\NurseInfo');
    }
}
