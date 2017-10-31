<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{



    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'lv_comments';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $dates = ['comment_date', 'comment_date_gmt'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','comment_content', 'user_id', 'comment_date', 'comment_date_gmt', 'comment_type', 'legacy_comment_id'];

    public function user()
    {
        return $this->belongsTo('App\User', 'id');
    }

    public function observation()
    {
        return $this->belongsTo('App\Observation', 'comment_ID');
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
}
