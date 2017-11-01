<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CareSection extends Model
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
    protected $table = 'care_sections';

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

    public function carePlans()
    {
        return $this->belongsToMany('App\CarePlan', 'care_item_care_plan', 'section_id', 'plan_id');
    }

    public function carePlanItems()
    {
        return $this->hasMany('App\CarePlanItem', 'section_id');
    }

    public static function boot()
    {
        parent::boot();

        /**
         * Automatically delete and item's meta when the item is deleted
         */
        CPRulesItem::deleting(function ($CPRulesItem) {
            $CPRulesItem->meta()->delete();
        });
    }
}
