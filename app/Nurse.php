<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    //nurse mapping for import csv
    public static $nurseMap = [
        'Patricia' => 1920,
        'Katie'    => 2159,
        'Lydia'    => 1755,
        'Sue'      => 1877,
        'Monique'  => 2332,
        'Erin'     => 2398,
        'Kerri'    => 2012,
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

    public function scopeActive()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->get();

    }

    public static function activeNursesForUI()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->pluck('display_name', 'id');


    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function summary()
    {
        return $this->hasMany(NurseMonthlySummary::class);
    }

    /**
     * Upcoming (future) contact windows.
     *
     * @return mixed
     */
    public function upcomingWindows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id')->upcoming();
    }       

    /**
     * Contact Windows (Schedule).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function windows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id');
    }
            
    public function calls()
    {

        return $this->hasMany('App\Call');
    }

    public function states()
    {

        return $this->belongsToMany(State::class, 'nurse_info_state');
    }
    
    public function callStatsForRange(Carbon $start, Carbon $end){

                            

    }

}
