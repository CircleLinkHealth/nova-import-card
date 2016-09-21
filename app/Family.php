<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{

    public function users(){

        $this->hasMany(PatientInfo::class, 'id', 'user_id');

    }


}
