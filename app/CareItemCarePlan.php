<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CareItemCarePlan extends Model {

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

    public function carePlan() {
        return $this->belongsTo('App\CarePlan', 'plan_id', 'id');
    }

    public function careItem() {
        return $this->belongsTo('App\CareItem', 'item_id', 'id');
    }

    public function careSection() {
        return $this->belongsTo('App\CareSection', 'section_id', 'id');
    }

    public function parents()
    {
        return $this->belongsTo('App\CareItemCarePlan', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\CareItemCarePlan', 'parent_id');
    }

}
