<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesIntrActions extends Model
{



    /**
     * The database table used by the model.
     * @SWG\Property()
     * @var string
     */
    protected $table = 'lv_rules_intr_actions';

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

    public function action()
    {
        return $this->belongsTo('App\RulesActions');
    }

    public function operator()
    {
        return $this->belongsTo('App\RulesOperators');
    }
}
