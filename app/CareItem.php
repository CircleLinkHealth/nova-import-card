<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CareItem
 *
 * @property int $id
 * @property string|null $model_field_name
 * @property int|null $type_id
 * @property string|null $type
 * @property string $relationship_fn_name
 * @property int $parent_id
 * @property int $qid
 * @property string $obs_key
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CareItem[] $children
 * @property-read mixed $meta_key
 * @property-read \App\CareItem $parents
 * @property-read \App\CPRulesQuestions $question
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereModelFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereObsKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereQid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereRelationshipFnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CareItem extends \App\BaseModel
{
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
