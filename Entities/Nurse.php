<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Customer\Traits\MakesOrReceivesCalls;

/**
 * CircleLinkHealth\Customer\Entities\Nurse.
 *
 * @property int                                                                                                $id
 * @property int                                                                                                $user_id
 * @property string                                                                                             $status
 * @property string                                                                                             $license
 * @property int                                                                                                $hourly_rate
 * @property string                                                                                             $billing_type
 * @property int                                                                                                $low_rate
 * @property int                                                                                                $high_rate
 * @property int                                                                                                $spanish
 * @property \Carbon\Carbon|null                                                                                $created_at
 * @property \Carbon\Carbon|null                                                                                $updated_at
 * @property int                                                                                                $isNLC
 * @property \CircleLinkHealth\Customer\Entities\NurseCareRateLog[]|\Illuminate\Database\Eloquent\Collection    $careRateLogs
 * @property mixed                                                                                              $holidays_this_week
 * @property mixed                                                                                              $upcoming_holiday_dates
 * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection             $holidays
 * @property \CircleLinkHealth\Customer\Entities\State[]|\Illuminate\Database\Eloquent\Collection               $states
 * @property \CircleLinkHealth\Customer\Entities\NurseMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $summary
 * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection             $upcomingHolidays
 * @property \CircleLinkHealth\Customer\Entities\User                                                           $user
 * @property \CircleLinkHealth\Customer\Entities\NurseContactWindow[]|\Illuminate\Database\Eloquent\Collection  $windows
 * @property \CircleLinkHealth\Customer\Entities\WorkHours[]|\Illuminate\Database\Eloquent\Collection           $workhourables
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereBillingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereHighRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereIsNLC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereLowRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereSpanish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereUserId($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse query()
 */
class Nurse extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Filterable;
    use MakesOrReceivesCalls;

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

    protected $fillable = [
        'user_id',
        'status',
        'license',
        'hourly_rate',
        'high_rate',
        'low_rate',
        'spanish',
        'isNLC',
        'is_demo',
        'pay_interval',
        'pay_algo',
    ];

    protected $table = 'nurse_info';

    public function calls()
    {
        return $this->user->outboundCalls();
    }

    public function callStatsForRange(Carbon $start, Carbon $end)
    {
    }

    public static function careGivenToPatientForCurrentMonthByNurse(Patient $patient, Nurse $nurse)
    {
        return \CircleLinkHealth\TimeTracking\Entities\Activity::where('provider_id', $nurse->user_id)
            ->where('patient_id', $patient->user_id)
            ->where(function ($q) {
                $q->where('created_at', '>=', Carbon::now()->startOfMonth())
                    ->where('updated_at', '<=', Carbon::now()->endOfMonth());
            })
            ->sum('duration');
    }

    public function careRateLogs()
    {
        return $this->hasMany(NurseCareRateLog::class);
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

        if ( ! $result) {
            $result = $weeklySchedule->first()[0];
        }

        if ( ! $result) {
            return false;
        }

        $result->date = $date->next(clhToCarbonDayOfWeek($result->day_of_week));

        return $result;
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

    public function getUpcomingHolidayDatesAttribute()
    {
        return $this->upcomingHolidays()
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat(
                    'Y-m-d',
                    "{$item->date->format('Y-m-d')}"
                );
            });
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

    public function states()
    {
        return $this->belongsToMany(State::class, 'nurse_info_state', 'nurse_info_id');
    }

    public function summary()
    {
        return $this->hasMany(NurseMonthlySummary::class);
    }

    /**
     * Upcoming days the Nurse is taking off.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function upcomingHolidays()
    {
        return $this->hasMany(Holiday::class, 'nurse_info_id', 'id')->where(
            'date',
            '>=',
            Carbon::now()->format('Y-m-d')
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    /**
     * Contact Windows (Schedule).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function windows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id');
    }

    /**
     * Get all the CircleLinkHealth\Customer\Entities\WorkHours attached to this CarePlan.
     */
    public function workhourables()
    {
        return $this->morphMany(WorkHours::class, 'workhourable');
    }
}
