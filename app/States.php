<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{

    public function nurses(){
        
        return $this->hasMany('App\NurseInfo');

    }

}
