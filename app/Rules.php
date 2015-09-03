<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Rules extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules';

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
