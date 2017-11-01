<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesItem extends Model
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
