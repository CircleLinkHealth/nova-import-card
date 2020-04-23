<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\TimeTracking\Entities\ActivityMeta.
 *
 * @property int                                              $id
 * @property int                                              $activity_id
 * @property int                                              $comment_id
 * @property string                                           $message_id
 * @property string|null                                      $meta_key
 * @property string                                           $meta_value
 * @property \Carbon\Carbon                                   $created_at
 * @property \Carbon\Carbon                                   $updated_at
 * @property \Carbon\Carbon|null                              $deleted_at
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity $activity
 * @method static                                           bool|null forceDelete()
 * @method static                                           \Illuminate\Database\Query\Builder|\App\ActivityMeta onlyTrashed()
 * @method static                                           bool|null restore()
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereActivityId($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCommentId($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCreatedAt($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereDeletedAt($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereId($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMessageId($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaKey($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaValue($value)
 * @method static                                           \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereUpdatedAt($value)
 * @method static                                           \Illuminate\Database\Query\Builder|\App\ActivityMeta withTrashed()
 * @method static                                           \Illuminate\Database\Query\Builder|\App\ActivityMeta withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta query()
 * @property int|null                                                                                    $revision_history_count
 */
class ActivityMeta extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['activity_id', 'meta_key', 'meta_value'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_activitymeta';

    public function activity()
    {
        return $this->belongsTo('CircleLinkHealth\TimeTracking\Entities\Activity');
    }
}
