<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CareSection
 *
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string $template
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanItem[] $carePlanItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CareSection extends \App\BaseModel
{



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
