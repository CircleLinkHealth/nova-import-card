<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesOperators extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_operators';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function intrConditions()
    {
        return $this->hasMany('App\RulesIntrConditions', 'operator_id');
    }

    public function intrActions()
    {
        return $this->hasMany('App\RulesIntrActions', 'operator_id');
    }
}
