<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesActions extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_actions';

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
