<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Rules extends Model
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
    protected $table = 'lv_rules';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    public function intrConditions()
    {
        return $this->hasMany('App\RulesIntrConditions', 'rule_id');
    }

    public function intrActions()
    {
        return $this->hasMany('App\RulesIntrActions', 'rule_id');
    }
}
