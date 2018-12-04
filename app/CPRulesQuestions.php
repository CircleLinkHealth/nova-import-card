<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Services\MsgUI;

/**
 * App\CPRulesQuestions.
 *
 * @property int                                                                 $qid
 * @property string                                                              $msg_id
 * @property string|null                                                         $qtype
 * @property string|null                                                         $obs_key
 * @property string|null                                                         $description
 * @property string                                                              $icon
 * @property string                                                              $category
 * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection            $careItems
 * @property mixed                                                               $msg_id_and_obs_key
 * @property \App\Observation[]|\Illuminate\Database\Eloquent\Collection         $observations
 * @property \App\CPRulesQuestionSets[]|\Illuminate\Database\Eloquent\Collection $questionSets
 * @property \App\CPRulesItem[]|\Illuminate\Database\Eloquent\Collection         $rulesItems
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereMsgId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereObsKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereQid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereQtype($value)
 * @mixin \Eloquent
 */
class CPRulesQuestions extends \App\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['qid', 'msg_id', 'qtype', 'obs_key', 'description', 'icon', 'category'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'qid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_questions';

    public function careItems()
    {
        return $this->hasMany('App\CareItem', 'qid', 'qid');
    }

    // ATTRIBUTES

    public function getMsgIdAndObsKeyAttribute()
    {
        $msgId  = $this->msg_id;
        $obsKey = $this->obs_key;

        return $msgId.' ['.$obsKey.']';
    }

    public function iconHtml()
    {
        $html    = '';
        $msgUI   = new MsgUI();
        $msgIcon = $msgUI->getMsgIcon($this->icon);
        if (!empty($msgIcon)) {
            $html = "<i style='color:".$msgIcon['color']."' class='fa fa-2x fa-".$msgIcon['icon']."'></i>";
        }

        return $html;
    }

    public function observations()
    {
        return $this->hasMany('App\Observation', 'msg_id', 'obs_message_id');
    }

    public function questionSets()
    {
        return $this->hasMany('App\CPRulesQuestionSets', 'qid', 'qid');
    }

    public function rulesItems() // rules prefix because ->items is a protect class var on parent
    {
        return $this->hasMany('App\CPRulesItem', 'qid', 'qid');
    }
}
