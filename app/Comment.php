<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Comment.
 *
 * @property int                                      $id
 * @property int                                      $comment_post_ID
 * @property string                                   $comment_author
 * @property string                                   $comment_author_email
 * @property string                                   $comment_author_url
 * @property string                                   $comment_author_IP
 * @property \Carbon\Carbon                           $comment_date
 * @property \Carbon\Carbon                           $comment_date_gmt
 * @property string                                   $comment_content
 * @property int                                      $comment_karma
 * @property string                                   $comment_approved
 * @property string                                   $comment_agent
 * @property string                                   $comment_type
 * @property int                                      $comment_parent
 * @property int                                      $user_id
 * @property int                                      $program_id
 * @property int                                      $legacy_comment_id
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Observation                         $observation
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAgent($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentApproved($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthor($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorEmail($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorIP($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorUrl($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentContent($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentDate($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentDateGmt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentKarma($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentParent($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentPostID($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentType($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereCreatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereLegacyCommentId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereProgramId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereUpdatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Comment whereUserId($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Comment newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Comment newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Comment query()
 * @property int|null                                                                                    $revision_history_count
 */
class Comment extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $dates = ['comment_date', 'comment_date_gmt'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'comment_content', 'user_id', 'comment_date', 'comment_date_gmt', 'comment_type', 'legacy_comment_id'];

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
    protected $table = 'lv_comments';

    public function observation()
    {
        return $this->belongsTo(\CircleLinkHealth\SharedModels\Entities\Observation::class, 'comment_ID');
    }

    public function save(array $params = [])
    {
        if (empty($this->user_id)) {
            dd('user_id is required for comment');
        }
        $wpUser = User::find($this->user_id);

        /*
        if(!$wpUser->program_id) {
            dd($this->user_id . ' is missing a program id');
        }
        */

        // take programId(blogId) and add to ma_X_comments table
        /*
        $params['comment_post_ID'] = $this->comment_post_ID;
        $params['comment_author'] = $this->comment_author;
        $params['comment_author_email'] = $this->comment_author_email;
        $params['comment_author_url'] = $this->comment_author_url;
        $params['comment_author_IP'] = $this->comment_author_IP;
        $params['comment_date'] = $this->comment_date;
        $params['comment_date_gmt'] = $this->comment_date_gmt;
        $params['comment_content'] = $this->comment_content;
        $params['comment_karma'] = $this->comment_karma;
        $params['comment_approved'] = $this->comment_approved;
        $params['comment_agent'] = $this->comment_agent;
        $params['comment_type'] = $this->comment_type;
        $params['comment_parent'] = $this->comment_parent;
        $params['user_id'] = $this->user_id;
        if(!$this->comment_karma) {
            $this->comment_karma = 0;
        }
        $params['comment_karma'] = $this->comment_karma;
        $this->program_id = $wpUser->program_id;
        */

        // updating or inserting?
        /*
        if($this->id) {
            DB::table('wp_' . $wpUser->primaryProgramId() . '_comments')->where('comment_ID', $this->legacy_comment_id)->update($params);
        } else {
            // add to legacy if doesnt already exist
            if(empty($this->legacy_comment_id)) {
                $resultCommentId = DB::table('wp_' . $wpUser->primaryProgramId() . '_comments')->insertGetId($params);
                $this->legacy_comment_id = $resultCommentId;
            }
        }
        */

        parent::save();
        // http://www.amitavroy.com/justread/content/articles/events-laravel-5-and-customize-model-save
    }

    public function user()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'id');
    }
}
