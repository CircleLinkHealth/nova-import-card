<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesActions extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'lv_rules_actions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function intrActions()
    {
        return $this->hasMany('App\RulesIntrActions', 'action_id');
    }
}
