<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CareItemUserValue extends Model {

    public $timestamps = false;
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
    protected $table = 'care_item_user_values';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'item_id', 'value'];

    public static function boot()
    {

    }

    public function careItem()
    {
        return $this->belongsTo('App\CareItem', 'item_id', 'id');
    }


    // START ATTRIBUTES

    // END ATTRIBUTES

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

}
