<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CPRulesQuestionSets.
 *
 * @property int                   $qsid
 * @property int                   $provider_id
 * @property string|null           $qs_type
 * @property int                   $qs_sort
 * @property int|null              $qid
 * @property int|null              $answer_response
 * @property int|null              $aid
 * @property int|null              $low
 * @property int|null              $high
 * @property string|null           $action
 * @property \App\CPRulesAnswers   $answer
 * @property \App\CPRulesQuestions $question
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAnswerResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsid($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets query()
 *
 * @property int|null $revision_history_count
 */
class CPRulesQuestionSets extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider_id', 'qs_type', 'qs_sort', 'qid', 'answer_response', 'aid', 'low', 'high', 'action'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'qsid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_question_sets';

    public function answer()
    {
        return $this->hasOne(\App\CPRulesAnswers::class, 'qid', 'qid');
    }

    public function question()
    {
        return $this->hasOne(\App\CPRulesQuestions::class, 'qid', 'qid');
    }
}
