<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesAnswers extends Model {

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
    protected $table = 'rules_answers';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'aid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['value', 'alt_answers', 'a_sort'];

    public $timestamps = false;


}
