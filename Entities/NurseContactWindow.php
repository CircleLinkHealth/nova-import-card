<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\NurseContactWindow.
 *
 * @property int                                       $id
 * @property int                                       $nurse_info_id
 * @property \Carbon\Carbon                            $date
 * @property int                                       $day_of_week
 * @property string                                    $window_time_start
 * @property string                                    $window_time_end
 * @property \Carbon\Carbon|null                       $created_at
 * @property \Carbon\Carbon|null                       $updated_at
 * @property \Carbon\Carbon|null                       $deleted_at
 * @property mixed                                     $day_name
 * @property \CircleLinkHealth\Customer\Entities\Nurse $nurse
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereNurseInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereWindowTimeEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereWindowTimeStart($value)
 * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow query()
 * @property int|null    $revision_history_count
 * @property string      $repeat_start
 * @property string|null $repeat_frequency
 * @property string|null $until
 * @property string      $validated
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow whereRepeatFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow whereRepeatStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow whereUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow whereValidated($value)
 */
class NurseContactWindow extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'date',
    ];

    protected $guarded = [];

    protected $primaryKey = 'id';

    protected $table = 'nurse_contact_window';

    public function getDayNameAttribute()
    {
        return clhDayOfWeekToDayName($this->day_of_week);
    }

    public function getScheduleForAllNurses()
    {
        return $this->with('nurse.user')
            ->where('date', '>=', Carbon::today()->format('Y-m-d'))
            ->get();
    }

    /**
     * These are not the same with 'Hours Committed' in the Nurse Daily Reports/Emails.
     * For those see: nurseInfo->workhourables().
     *
     * @return int
     */
    public function numberOfHoursCommitted()
    {
        $start = $this->date->copy()->setTimeFromTimeString($this->window_time_start);
        $end   = $this->date->copy()->setTimeFromTimeString($this->window_time_end);

        return $start->diffInHours($end);
    }

    // END RELATIONSHIPS

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id', 'id');
    }

    /**
     * Returns the range of the windows as an object consisting of 2 Carbon Objects.
     *
     * @return \stdClass
     */
    public function range()
    {
        $object = new \stdClass();

        $object->start = Carbon::parse($this->window_time_start);
        $object->end   = Carbon::parse($this->window_time_end);

        return $object;
    }

    /**
     * Scope query to only include upcoming (future) windows.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', Carbon::today()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->orderBy('window_time_start', 'asc');
    }

    // START RELATIONSHIPS

    /**
     * Delete all current call windows. Then add the ones given.
     * Returns an array of contact windows created.
     *
     * @param $dayOfWeek
     * @param $startTime
     * @param $endTime
     *
     * @return static
     */
    public static function sync(
        Nurse $info,
        $dayOfWeek,
        $startTime,
        $endTime
    ) {
        //first delete all call windows
        $info->windows()->delete();

        return self::create([
            'nurse_info_id'     => $info->id,
            'day_of_week'       => $dayOfWeek,
            'window_time_start' => $startTime,
            'window_time_end'   => $endTime,
        ]);
    }
}
