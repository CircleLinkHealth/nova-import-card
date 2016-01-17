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
    protected $table = 'care_plans';

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

    public function careItems() {
        return $this->belongsToMany('App\CareItem', 'care_item_care_plan', 'plan_id', 'item_id')->withPivot('id');
    }

    public function carePlanItems() {
        return $this->hasMany('App\CareItemCarePlan', 'plan_id', 'id');
    }

    public function careSections() {
        return $this->belongsToMany('App\CareSection', 'care_plan_care_section', 'plan_id', 'section_id')->withPivot('id', 'section_id');
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
