<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Customer\Traits\MakesOrReceivesCalls;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;

/**
 * CircleLinkHealth\Customer\Entities\Nurse.
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property string              $status
 * @property string              $license
 * @property int                 $hourly_rate
 * @property string              $billing_type
 * @property int                 $low_rate
 * @property int                 $high_rate
 * @property int                 $spanish
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int                 $isNLC
 * @property \CircleLinkHealth\Customer\Entities\NurseCareRateLog[]|\Illuminate\Database\Eloquent\Collection
 *     $careRateLogs
 * @property mixed                                                                                  $holidays_this_week
 * @property mixed                                                                                  $upcoming_holiday_dates
 * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection $holidays
 * @property \CircleLinkHealth\Customer\Entities\State[]|\Illuminate\Database\Eloquent\Collection   $states
 * @property \CircleLinkHealth\Customer\Entities\NurseMonthlySummary[]|\Illuminate\Database\Eloquent\Collection
 *     $summary
 * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection            $upcomingHolidays
 * @property \CircleLinkHealth\Customer\Entities\User                                                          $user
 * @property \CircleLinkHealth\Customer\Entities\NurseContactWindow[]|\Illuminate\Database\Eloquent\Collection $windows
 * @property \CircleLinkHealth\Customer\Entities\WorkHours[]|\Illuminate\Database\Eloquent\Collection          $workhourables
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
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse
 *     filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse query()
 * @property int $is_demo
 * @property int $pay_interval
 * @property int $is_variable_rate
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice[] $invoices
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse whereIsDemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse whereIsVariableRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse wherePayInterval($value)
 * @property-read int|null $care_rate_logs_count
 * @property-read int|null $holidays_count
 * @property-read int|null $invoices_count
 * @property-read int|null $revision_history_count
 * @property-read int|null $states_count
 * @property-read int|null $summary_count
 * @property-read int|null $windows_count
 * @property-read int|null $workhourables_count
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
        'is_variable_rate',
        //"Case Load Capacity" is an integer denoting the target number of patients a nurse should be handling.
        'case_load_capacity'
    ];

    protected $table = 'nurse_info';

    /**
     * @var array A cache variable to make sure the query to db happens once during the lifetime of the process
     */
    private $companyHolidaysCache = [];

    public function calls()
    {
        if ($this->relationLoaded('user')) {
            return $this->user->calls();
        }

        return $this->user()->firstOrFail()->calls();
    }

    public function callStatsForRange(Carbon $start, Carbon $end)
    {
    }

    public static function careGivenToPatientForCurrentMonthByNurse(Patient $patient, Nurse $nurse)
    {
        return \CircleLinkHealth\TimeTracking\Entities\Activity::where('provider_id', $nurse->user_id)
            ->where('patient_id', $patient->user_id)
            ->where(function ($q) {
                                                                   $q->where(
                                                                       'created_at',
                                                                       '>=',
                                                                       Carbon::now()->startOfMonth()
                                                                   )
                                                                       ->where(
                                                                         'updated_at',
                                                                         '<=',
                                                                         Carbon::now()->endOfMonth()
                                                                     );
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
        $holidaysThisWeek = $this->upcomingHolidaysFrom(Carbon::today())
            ->map(function ($holiday) {
                                     if ($holiday->date->lte(Carbon::now()->endOfWeek()) && $holiday->date->gte(Carbon::now()->startOfWeek())) {
                                         return clhDayOfWeekToDayName(carbonToClhDayOfWeek($holiday->date->dayOfWeek));
                                     }
                                 });

        return array_filter($holidaysThisWeek->all());
    }

    /**
     * @param Carbon $date
     *
     * @return int
     */
    public function getHoursCommittedForCarbonDate(Carbon $date)
    {
        $workHours = $this->workhourables->first();

        if ( ! is_null($workHours)) {
            return (int) $workHours->{strtolower(
                $date->format('l')
            )};
        }

        return 0;
    }

    public function getUpcomingHolidayDatesAttribute()
    {
        return $this->upcomingHolidaysFrom(Carbon::today())
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(NurseInvoice::class, 'nurse_info_id');
    }

    /**
     * Returns true or false if the date passed is a holiday for this nurse.
     *
     * @param Carbon                $date
     * @param CompanyHoliday[]|null $companyHolidays
     *
     * @return bool
     */
    public function isOnHoliday(Carbon $date, $companyHolidays = null): bool
    {
        $isNurseHoliday = $this->holidays->where('date', $date->copy()->startOfDay())->count() > 0;
        if ($isNurseHoliday) {
            return true;
        }

        if ( ! $companyHolidays) {
            $companyHolidays = CompanyHoliday::query();
        }

        return $companyHolidays->where('holiday_date', '=', $date->toDateString())->count() > 0;
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
     * NOTE: Company holidays included. Those entries do not have an `id`.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function upcomingHolidaysFrom(Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::today();
        }

        $companyHolidays = $this->companyHolidaysFrom($date);
        $nurseHolidays   = $this->holidays->where('date', '>=', $date->copy()->startOfDay());

        return $companyHolidays->merge($nurseHolidays)->unique();
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
     * These are the time-frames per day of the week that the nurse scheduled to work.
     * These are not the 'hours commited' per day.
     * Hours committed (see workhourables()) exist within these windows.
     *
     * Example:
     * NurseContactWindow: day of week: 1 (monday), window_time_start: 09:00:00, window_time_end: 19:00:00.
     * Workhourables: monday: 2
     * This nurse has committed to working 2 hours within 09:00 - 19:00 on Monday.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function windows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id');
    }

    /**
     * These are the hours committed by each Nurse for each day of the week.
     * These will exist within a time-frame (see windows())for each day of the week.
     *
     * Example:
     * NurseContactWindow: day of week: 1 (monday), window_time_start: 09:00:00, window_time_end: 19:00:00.
     * Workhourables: monday: 2
     * This nurse has committed to working 2 hours within 09:00 - 19:00 on Monday.
     */
    public function workhourables()
    {
        return $this->morphMany(WorkHours::class, 'workhourable');
    }

    /**
     * Get company holidays from a date.
     *
     * @param Carbon|null $date
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function companyHolidaysFrom(Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::today();
        }

        $dateStr = $date->format('Y-m-d');

        if (isset($this->companyHolidays[$dateStr])) {
            return $this->companyHolidays[$dateStr];
        }

        $companyHolidays = CompanyHoliday::where('holiday_date', '>=', $dateStr)
            ->get()
            ->map(function (CompanyHoliday $h) {
                                             $nurseHoliday = new Holiday();
                                             $nurseHoliday->date = $h->holiday_date;
                                             $nurseHoliday->nurse_info_id = $this->id;

                                             return $nurseHoliday;
                                         });

        $this->companyHolidaysCache[$dateStr] = $companyHolidays;

        return $companyHolidays;
    }
}
