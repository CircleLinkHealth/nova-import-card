<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesItem extends Model {

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


    public function meta()
    {
        return $this->hasMany('App\CPRulesItemMeta', 'items_id');
    }

    public function getRulesItem($itemId)
    {
        $rulesUCP = CPRulesUCP::where('items_id', '=', $itemId)->get();

        return $rulesUCP;
    }

}
