<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CareItem extends Model
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
    protected $table = 'care_items';

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
    protected $fillable = [
        'parent_id',
        'name',
        'display_name',
        'description',
    ];

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

    public function carePlans()
    {
        return $this->belongsToMany('App\CarePlan', 'care_plan_care_item', 'item_id', 'plan_id')->withPivot('id');
    }

    public function userValues()
    {
        return $this->hasMany('App\CareItemUserValue', 'care_item_id', 'id');
    }

    public function question() // rules prefix because ->items is a protect class var on parent
    {
        return $this->belongsTo('App\CPRulesQuestions', 'qid', 'qid');
    }

    public function parents()
    {
        return $this->belongsTo('App\CareItem', 'parent_id');
    }


    // START ATTRIBUTES

    public function children()
    {
        return $this->hasMany('App\CareItem', 'parent_id');
    }

    // END ATTRIBUTES

    public function getMetaKeyAttribute()
    {
        return $this->pivot->meta_key;
    }

}
