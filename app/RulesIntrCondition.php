<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesIntrCondition extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_intr_conditions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';



    public function rules()
    {
        return $this->belongsTo('App\RulesIntrCondition');
    }

}
