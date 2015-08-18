<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model {

    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wp_7_comments';

    // telling laravel not to create timestamps by default
    public $timestamps = false;


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'comment_ID';

    protected $dates = ['comment_date', 'comment_date_gmt'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['comment_ID','comment_content', 'user_id', 'comment_date', 'comment_date_gmt', 'comment_type'];

    public function user()
    {
        return $this->belongsTo('App\WpUser', 'ID');
    }

    public function observation()
    {
        return $this->belongsTo('App\Observation', 'comment_ID');
    }


}
