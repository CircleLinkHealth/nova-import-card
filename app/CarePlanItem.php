<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CarePlanItem
 *
 * @property-read \App\CareItem $careItem
 * @property-read \App\CarePlan $carePlan
 * @property-read \App\CareSection $careSection
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanItem[] $children
 * @property-read \App\CarePlanItem $parents
 * @mixin \Eloquent
 */
class CarePlanItem extends \App\BaseModel
{

    public $timestamps = false;



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'care_item_care_plan';

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

    public function carePlan()
    {
        return $this->belongsTo('App\CarePlan', 'plan_id', 'id');
    }

    public function careItem()
    {
        return $this->belongsTo('App\CareItem', 'item_id', 'id');
    }

    public function careSection()
    {
        return $this->belongsTo('App\CareSection', 'section_id', 'id');
    }

    public function parents()
    {
        return $this->belongsTo('App\CarePlanItem', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\CarePlanItem', 'parent_id');
    }
}
