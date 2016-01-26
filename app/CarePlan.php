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
        return $this->hasMany('App\CarePlanItem', 'plan_id', 'id');
    }

    public function careSections() {
        return $this->belongsToMany('App\CareSection', 'care_plan_care_section', 'plan_id', 'section_id')->withPivot('id', 'section_id', 'status');
    }

    public function build($userId = false) {
        $this->user_id = false;
        if($userId) {
            $this->user_id = $userId;
        }
        // build careplan
        foreach($this->careSections as $careSection) {
            // add parent items to each section
            $careSection->carePlanItems = $this->carePlanItems()
                ->where('section_id', '=', $careSection->id)
                ->where('parent_id', '=', 0)
                ->orderBy('ui_sort', 'asc')
                ->with(array('children' => function ($query) {
                    $query->orderBy('ui_sort', 'asc');
                }))
                ->get();
            // user override
            if($userId) {
                $user = User::find($userId);
                if($user) {
                    if ($careSection->carePlanItems->count() > 0) {
                        foreach ($careSection->carePlanItems as $carePlanItem) {
                            // parents
                            $carePlanItem->meta_value = $this->getCareItemUserValue($user, $carePlanItem->careItem->name);
                            // children
                            if ($carePlanItem->children->count() > 0) {
                                foreach ($carePlanItem->children as $carePlanItemChild) {
                                    //if($carePlanItemChild->item_id == '42') {
                                        //dd($carePlanItem->careItem->name);
                                        $carePlanItemChild->meta_value = $this->getCareItemUserValue($user, $carePlanItemChild->careItem->name);
                                        //dd($carePlanItemChild);
                                    //}
                                }
                            }
                        }
                    }
                }
            }
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

    public function getCareItemUserValue(User $user, $name) {
        $careItem = $this->careItems()->where('name','=',$name)->withPivot('id','meta_value')->first();
        if(!$careItem) {
            return false;
        }
        $userCareItemValue = $user->careItems()->where('name', '=', $careItem->name)->first();
        if($userCareItemValue) {
            return $userCareItemValue->pivot->value;
        }
        return '';
    }

    public function setCareItemUserValue(User $user, $name, $value) {
        $careItem = $this->careItems()->where('name','=',$name)->withPivot('meta_value')->first();
        if(!$careItem) {
            return false;
        }
        $userCareItemValue = $user->careItems()->where('name', '=', $careItem->name)->first();
        if($userCareItemValue) {
            $userCareItemValue->pivot->value = $value;
            $userCareItemValue->pivot->save();
        }
        return true;
    }



}
