<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CarePlan extends Model {

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
    protected $table = 'lv_care_plans';

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
    protected $fillable = ['parent_id', 'name', 'display_name', 'description'];

    public function ucp()
    {
        return $this->hasMany('App\CareplanUcp', 'id', 'ucp_id');
    }

    public function item()
    {
        return $this->hasMany('App\Careplan', 'qid', 'qid');
    }

    public static function boot()
    {
        parent::boot();

        /**
         * Automatically delete and item's meta when the item is deleted
         */
        CPRulesItem::deleting(function($CPRulesItem){
            $CPRulesItem->meta()->delete();
        });
    }

}
