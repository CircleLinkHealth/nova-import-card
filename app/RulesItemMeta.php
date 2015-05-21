<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesItemMeta extends Model {

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
    protected $table = 'wp_rules_itemmeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'itemmeta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['itemmeta_id', 'items_id', 'meta_key', 'meta_value'];

    public function rulesItem()
    {
        return $this->belongsTo('App\RulesItem', 'items_id');
    }



}
