<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseInfo extends Model
{

    //Relationships:

    protected $table = 'nurse_info';

    public function user()
    {
        return $this->belongsTo('App\User', 'ID', 'user_id');
    }

    public function calls(){
    
        return $this->hasMany('App\Call');
    }

    public function states(){

        return $this->belongsToMany('App\States');
    }


}
