<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseInfo extends Model
{
    protected $table = 'nurse_info';

    protected $fillable = [
        'user_id',
        'status',
        'license',
        'hourly_rate',
        'spanish',
        'isNLC',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'ID', 'user_id');
    }

    public function windows()
    {
        return $this->hasMany('App\NurseContactWindow', 'nurse_info_id', 'id');
    }

    public function calls(){
    
        return $this->hasMany('App\Call');
    }

    public function states(){

        return $this->belongsToMany(State::class, 'nurse_info_state');
    }


}
