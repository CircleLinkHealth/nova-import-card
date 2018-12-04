<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ActivityMeta
 *
 * @property int $id
 * @property int $activity_id
 * @property int $comment_id
 * @property string $message_id
 * @property string|null $meta_key
 * @property string $meta_value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Activity $activity
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta withoutTrashed()
 * @mixin \Eloquent
 */
class ActivityMeta extends \App\BaseModel
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_activitymeta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['activity_id', 'meta_key', 'meta_value'];

    protected $dates = ['deleted_at'];

    public function activity()
    {
        return $this->belongsTo('App\Activity');
    }
}
