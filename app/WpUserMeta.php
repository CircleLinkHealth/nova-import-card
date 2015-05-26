<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class WpUserMeta extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wp_usermeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['umeta_id', 'user_id', 'meta_key', 'meta_value'];

    public $timestamps = false;

    public function wpUser()
    {
        return $this->belongsTo('App\WpUser', 'user_id', 'ID');
    }



}
