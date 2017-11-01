<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesQuestionSets extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_question_sets';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'qsid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider_id', 'qs_type', 'qs_sort', 'qid', 'answer_response', 'aid', 'low', 'high', 'action'];

    public $timestamps = false;

    public function question()
    {
        return $this->hasOne('App\CPRulesQuestions', 'qid', 'qid');
    }

    public function answer()
    {
        return $this->hasOne('App\CPRulesAnswers', 'qid', 'qid');
    }
}
