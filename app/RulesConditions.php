<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesConditions extends Model
{



    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'lv_rules_conditions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function intrConditions()
    {
        return $this->hasMany('App\RulesIntrConditions', 'condition_id');
    }
}
