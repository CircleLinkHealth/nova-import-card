<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseContactWindow extends Model
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
     * @param NurseInfo $info
     * @param $dayOfWeek
     * @param $startTime
     * @param $endTime
     *
     * @return static
     */
    public static function sync(
        NurseInfo $info,
        $dayOfWeek,
        $startTime,
        $endTime
    ) {
        //first delete all call windows
        $info->windows()->delete();

        return self::create([
            'nurse_info_id' => $info->id,
            'day_of_week' => $dayOfWeek,
            'window_time_start' => $startTime,
            'window_time_end' => $endTime,
        ]);
    }

    // END RELATIONSHIPS

    public function nurse()
    {
        return $this->belongsTo(NurseInfo::class, 'nurse_info_id', 'id');
    }

}
