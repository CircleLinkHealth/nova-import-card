<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseInfo extends Model
{
    //nurse mapping for import csv
    public static $nurseMap = [
        'Patricia' => 1920,
        'Katie'    => 2159,
        'Lydia'    => 1755,
        'Sue'      => 1877,
        'Monique'  => 2332,
        'Erin'     => 2398,
    ];

    protected $table = 'nurse_info';

    protected $fillable = [
        'user_id',
        'status',
        'license',
        'hourly_rate',
        'spanish',
        'isNLC',
    ];

    public function scopeActive(){

        return NurseInfo::whereStatus('active');

    }

    public function activeNursesForUI(){

        return User::whereHas('nurseInfo', function ($query) {
                    $query->whereStatus('active');
                })->pluck('display_name','ID');
        
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'ID', 'user_id');
    }
    
    public function summary()
    {
        return $this->hasMany(NurseMonthlySummary::class);
    }

    public function windows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id');
    }

    public function calls(){
    
        return $this->hasMany('App\Call');
    }

    public function states(){

        return $this->belongsToMany(State::class, 'nurse_info_state');
    }


}
