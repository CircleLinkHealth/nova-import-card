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

    var $pivotColumns = [
        'meta_key',
        'meta_value',
        'alert_key',
        'ui_placeholder',
        'ui_default',
        'ui_title',
        'ui_fld_type',
        'ui_show_detail',
        'ui_row_start',
        'ui_row_end',
        'ui_sort',
        'ui_col_start',
        'ui_col_end',
        'track_as_observation',
        'APP_EN',
        'APP_ES'
    ];


    public function careItems() {
        return $this->belongsToMany('App\CareItem', 'care_item_care_plan', 'plan_id', 'item_id')->withPivot('id');
    }

    public function carePlanItems() {
        return $this->hasMany('App\CareItemCarePlan', 'plan_id', 'id');
    }

    public function careSections() {
        return $this->belongsToMany('App\CareSection', 'care_plan_care_section', 'plan_id', 'section_id')->withPivot('id', 'section_id');
    }

    public function build() {
        // build careplan
        foreach($this->careSections as $careSection) {
            // add parent items to each section
            $careSection->planItems = $this->carePlanItems()
                ->where('section_id', '=', $careSection->id)
                ->where('parent_id', '=', 0)
                ->orderBy('ui_sort', 'asc')
                ->with(array('children' => function ($query) {
                    $query->orderBy('ui_sort', 'asc');
                }))
                ->get();
        }
    }


    public static function boot()
    {
        parent::boot();

        /**
         * Automatically delete and item's meta when the item is deleted
         */
        /*
        CPRulesItem::deleting(function($CPRulesItem){
            $CPRulesItem->meta()->delete();
        });
        */
    }

    public function getCareItemValue($name) {
        $careItem = $this->careItems()->where('name','=',$name)->withPivot('id','meta_value')->first();
        if(!$careItem) {
            return false;
        }
        return $careItem->pivot->meta_value;
    }

    public function setCareItemValue($name, $value) {
        $careItem = $this->careItems()->where('name','=',$name)->withPivot('meta_value')->first();
        if(!$careItem) {
            return false;
        }
        $careItem->pivot->meta_value = $value;
        $careItem->pivot->save();
        return true;
    }



}
