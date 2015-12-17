<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CarePlanSection extends Model {

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
    protected $table = 'lv_care_plan_sections';

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
    protected $fillable = ['plan_id', 'display_name', 'description'];

    public function careplan()
    {
        return $this->hasMany('App\CarePlan', 'id', 'plan_id');
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
