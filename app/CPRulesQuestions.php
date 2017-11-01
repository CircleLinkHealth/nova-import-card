<?php namespace App;

use App\Services\MsgUI;
use Illuminate\Database\Eloquent\Model;

class CPRulesQuestions extends Model
{

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
    protected $fillable = ['qid', 'msg_id', 'qtype', 'obs_key', 'description', 'icon', 'category'];

    public $timestamps = false;


    public function rulesItems() // rules prefix because ->items is a protect class var on parent
    {
        return $this->hasMany('App\CPRulesItem', 'qid', 'qid');
    }

    public function careItems()
    {
        return $this->hasMany('App\CareItem', 'qid', 'qid');
    }

    public function questionSets()
    {
        return $this->hasMany('App\CPRulesQuestionSets', 'qid', 'qid');
    }

    public function observations()
    {
        return $this->hasMany('App\Observation', 'msg_id', 'obs_message_id');
    }


    public function iconHtml()
    {
        $html = '';
        $msgUI = new MsgUI;
        $msgIcon = $msgUI->getMsgIcon($this->icon);
        if (!empty($msgIcon)) {
            $html = "<i style='color:" . $msgIcon['color'] . "' class='fa fa-2x fa-" . $msgIcon['icon'] . "'></i>";
        }
        return $html;
    }



    // ATTRIBUTES

    public function getMsgIdAndObsKeyAttribute()
    {
        $msgId = $this->msg_id;
        $obsKey = $this->obs_key;
        return $msgId . ' [' . $obsKey . ']';
    }
}
