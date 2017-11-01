<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesItemMeta extends Model
{

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
    protected $table = 'rules_itemmeta';

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

    public $timestamps = false;

    public function CPRulesItem()
    {
        return $this->belongsTo('App\CPRulesItem', 'items_id');
    }
}
