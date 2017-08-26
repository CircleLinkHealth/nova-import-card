<?php

namespace App;

use App\Models\Holiday;
use App\Models\WorkHours;
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
        'high_rate',
        'low_rate',
        'spanish',
        'isNLC',
    ];

    public static function activeNursesForUI()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->pluck('display_name', 'id');


    }

    public static function careGivenToPatientForCurrentMonthByNurse(Patient $patient, Nurse $nurse)
    {

        return Activity::where('provider_id', $nurse->user_id)
            ->where('patient_id', $patient->user_id)
            ->where(function ($q) {
                $q->where('created_at', '>=', Carbon::now()->startOfMonth())
                    ->where('updated_at', '<=', Carbon::now()->endOfMonth());
            })
            ->sum('duration');
    }

    public function scopeActive()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->get();

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
     * Days the Nurse is taking off.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'nurse_info_id', 'id');
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

    public function careRateLogs()
    {

        return $this->hasMany(NurseCareRateLog::class);

    }

    public function callStatsForRange(Carbon $start, Carbon $end)
    {


    }

    public function getUpcomingHolidayDatesAttribute()
    {
        return $this->upcomingHolidays()
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d',
                    "{$item->date->format('Y-m-d')}");
            });
    }

    /**
     * Upcoming days the Nurse is taking off.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function upcomingHolidays()
    {
        return $this->hasMany(Holiday::class, 'nurse_info_id', 'id')->where('date', '>=',
            Carbon::now()->format('Y-m-d'));
    }

    public function getHolidaysThisWeekAttribute()
    {
        $holidaysThisWeek = $this->upcomingHolidays()
            ->get()
            ->map(function ($holiday) {
                if ($holiday->date->lte(Carbon::now()->endOfWeek()) && $holiday->date->gte(Carbon::now()->startOfWeek())) {
                    return clhDayOfWeekToDayName(carbonToClhDayOfWeek($holiday->date->dayOfWeek));
                }
            });

        return array_filter($holidaysThisWeek->all());
    }

    /**
     * Get all the App\Models\WorkHours attached to this CarePlan.
     */
    public function workhourables()
    {
        return $this->morphMany(WorkHours::class, 'workhourable');
    }

    public function firstWindowAfter(Carbon $date)
    {
        $dayOfWeek = carbonToClhDayOfWeek($date->dayOfWeek);

        $weeklySchedule = $this->weeklySchedule();

        $result = null;

        foreach ($weeklySchedule as $day => $windows) {
            if ($day > $dayOfWeek) {
                $result = $windows[0];
                break;
            }
        }

        if (!$result) {
            $result = $weeklySchedule->first()[0];
        }

        if (!$result) {
            return false;
        }

        $result->date = $date->next(clhToCarbonDayOfWeek($result->day_of_week));

        return $result;
    }

    public function weeklySchedule()
    {
        $schedule = [];

        foreach ($this->windows->sortBy('window_time_start') as $window) {
            $schedule[$window->day_of_week][] = $window;
        }

        ksort($schedule);

        return collect($schedule);
    }

    public function scheduledCallsForToday()
    {
        return Call::where([
            ['outbound_cpm_id', '=', $this->user->id],
            ['scheduled_date', '>=', Carbon::now()->startOfDay()],
            ['scheduled_date', '<=', Carbon::now()->endOfDay()],
            ['status', '=', 'scheduled'],
        ])->get();
    }
}
