<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseContactWindow extends \App\BaseModel
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

    protected $table = 'nurse_contact_window';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    // START RELATIONSHIPS

    /**
     * Delete all current call windows. Then add the ones given.
     * Returns an array of contact windows created.
     *
     * @param Nurse $info
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

    // END RELATIONSHIPS

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id', 'id');
    }

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


    /**
     * Returns the range of the windows as an object consisting of 2 Carbon Objects
     *
     * @return \stdClass
     */
    public function range()
    {
        $object = new \stdClass();

        $object->start = Carbon::parse($this->window_time_start);
        $object->end = Carbon::parse($this->window_time_end);

        return $object;
    }
}
