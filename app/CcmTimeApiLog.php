<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\CcmTimeApiLog
 *
 * @property int $id
 * @property int $activity_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Activity $activity
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CcmTimeApiLog extends \App\BaseModel implements Transformable
{
    use TransformableTrait;

    protected $guarded = [];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
