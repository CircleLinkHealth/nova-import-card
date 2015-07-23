<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesIntrConditions extends Model {

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



    public function rule()
    {
        return $this->belongsTo('App\Rules');
    }

    public function condition()
    {
        return $this->belongsTo('App\RulesConditions');
    }

    public function operator()
    {
        return $this->belongsTo('App\RulesOperators');
    }

}
