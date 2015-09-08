<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesQuestions extends Model {

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
    protected $table = 'rules_questions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'qid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['qid', 'msg_id', 'qtype', 'obs_key', 'description'];


    public function items()
    {
        return $this->hasMany('App\CPRulesItem', 'qid');
    }

}
