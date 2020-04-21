<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;

/**
 * App\ObservationMeta.
 *
 * @property int              $id
 * @property int              $obs_id
 * @property int              $comment_id
 * @property string           $message_id
 * @property string           $meta_key
 * @property string           $meta_value
 * @property int              $program_id
 * @property int              $legacy_meta_id
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 * @property \App\Observation $observationMeta
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereCommentId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereCreatedAt($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereLegacyMetaId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMessageId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMetaKey($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMetaValue($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereObsId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereProgramId($value)
 * @method   static           \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta query()
 * @property int|null                                                                                    $revision_history_count
 */
class ObservationMeta extends \CircleLinkHealth\Core\Entities\BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'obs_id', 'comment_id', 'message_id', 'legacy_meta_id', 'meta_key', 'meta_value'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_observationmeta';

    public function observationMeta()
    {
        return $this->belongsTo(\App\Observation::class, 'obs_id');
    }

    public function save(array $params = [])
    {
        if (empty($this->obs_id)) {
            return false;
        }
        $observation = Observation::find($this->obs_id);
        $comment     = Comment::find($observation->comment_id);
        $wpUser      = User::find($observation->user_id);

        if ( ! $wpUser || ! $observation) {
            return false;
        }

        /*
         * // NO LONGER NEEDED IN 3.0, REMOVING LEGACY MA_* TABLES
         *
        // take programId(primaryProgramId) and add to wp_X_observationmeta table
        $params['obs_id'] = $observation->legacy_obs_id;
        if($comment) {
            $params['comment_id'] = $comment->legacy_comment_id;
        } else {
            $this->comment_id = '0';
            $params['comment_id'] = '0';
        }
        $params['message_id'] = $this->message_id;
        $params['meta_key'] = $this->meta_key;
        $params['meta_value'] = $this->meta_value;
        $this->program_id = $wpUser->primaryProgramId();

        // updating or inserting?
        if($this->id) {
            DB::table('ma_'.$wpUser->primaryProgramId().'_observationmeta')->where('comment_ID', $this->legacy_meta_id)->update($params);
        } else {
            // add to legacy if doesnt already exist
            if(empty($this->legacy_meta_id)) {
                $resultId = DB::table('ma_' . $wpUser->primaryProgramId() . '_observationmeta')->insertGetId($params);
                $this->legacy_meta_id = $resultId;
            }
        }
        */

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }
}
