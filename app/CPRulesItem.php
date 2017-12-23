<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CPRulesItem
 *
 * @property int $items_id
 * @property int|null $pcp_id
 * @property int|null $items_parent
 * @property int|null $qid
 * @property string $care_item_id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string|null $items_text
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CPRulesItemMeta[] $meta
 * @property-read \App\CPRulesPCP|null $pcp
 * @property-read \App\CPRulesQuestions|null $question
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem wherePcpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereQid($value)
 * @mixin \Eloquent
 */
class CPRulesItem extends \App\BaseModel
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_items';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'items_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['items_id', 'pcp_id', 'items_parent', 'qid', 'items_text'];

    public $timestamps = false;


    public function meta()
    {
        return $this->hasMany('App\CPRulesItemMeta', 'items_id');
    }

    public function pcp()
    {
        return $this->belongsTo('App\CPRulesPCP', 'pcp_id');
    }

    public function question()
    {
        return $this->belongsTo('App\CPRulesQuestions', 'qid', 'qid');
    }

    public function getRulesItem($itemId)
    {
        $rulesUCP = CPRulesUCP::where('items_id', '=', $itemId)->get();

        return $rulesUCP;
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
