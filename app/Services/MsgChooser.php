<?php namespace App\Services;

use App\WpUser;
use App\WpUserMeta;
use App\Services\MsgCPRules;
use App\Services\MsgSubstitutions;
use App\Services\MsgTod;
use DB;
/*
$this->_ci->load->model('cpm_1_7_rules_model','rules');
$this->_ci->load->library('cpm_1_7_substitution_library');
$this->_ci->load->library('cpm_1_7_tod_library');
*/

class MsgChooser {

    /**
     * Msgchooser picks next action to take for questions.
     * Used for SMARTWOMAN™
     *
     * @author mrand@circlelinkhealth.com
     * @copyright CircleLink Health, LLC - 01/30/2015
     *
     */

    private $_ci;
    private $arrReturn;
    private $key;
    private $comment_id;
    private $provid;
    private $smsMeth;
    private $programId;

    public function __construct() {
    }


    public function setNextMessage($programId, $commentId, $msgId, $answer, $qsType) {
        echo "<br>MsgChooser->setNextMessage() start";

        // get comment
        $query = DB::connection('mysql_no_prefix')->table('wp_'.$programId.'_comments AS cm');
        $query->select('cm.*');
        $query->where('comment_ID', '=', $commentId);
        $query->limit('1');
        $comment = $query->first();
        if(empty($comment)) {
            echo "<br>MsgChooser->setNextMessage() comment not found";
            return false;
        }

        // instantiate user
        $userId = $comment->user_id;
        $wpUser = WpUser::find($userId);
        if (!$wpUser) {
            echo "<br>MsgChooser->setNextMessage() user not found";
            return false;
        }
        $msgUser = new MsgUser;
        $userMeta = $wpUser->userMeta();

        // loop through comment_content and find matching msgId (this doesnt do anything yet)
        $commentContent = unserialize($comment->comment_content);
        echo "<br>MsgChooser->setNextMessage() message list";
        $msgIdMatch = false;
        foreach($commentContent as $row => $msg) {
        }

        // current message info
        $msgCPRules = new MsgCPRules;
        $currQuestionInfo  = $msgCPRules->getQuestion($msgId, $userId, 'SMS_EN', $programId, $qsType);
        echo '<br>MsgChooser->setNextMessage() currQuestion->qtype = '.$currQuestionInfo->qtype.'';

        // get answerResponse
        $msgCPRules = new MsgCPRules;
        $answerResponse =  $msgCPRules->getValidAnswer($programId, $qsType, $msgId, $answer);
        if(!$answerResponse) {
            echo '<br>MsgChooser->setNextMessage() getValidAnswer result FAIL, die..';die();
            return false;
        }
        echo '<br>MsgChooser->setNextMessage() getValidAnswer result - qsid='.$answerResponse->qsid.' | qid='.$answerResponse->qid.' | aid='.$answerResponse->aid.' | action='.$answerResponse->action;

        // extra processing (havent had this hit yet, this block needs work)
        if(!empty($answerResponse->action) && ($currQuestionInfo->qtype == 'None')) {
            if(strpos($answerResponse->action, '(') === FALSE) {
                $tmpfunc = $answerResponse->action;
                echo "<br>MsgChooser->setNextMessage() [[ 1 ]] tmpfunc = ".$tmpfunc;
                $tmpMsgId = $this->$tmpfunc();
                echo "<br>MsgChooser->setNextMessage() [[ 2 ]] tmpMsgId = ".$tmpfunc;
            } else {
                echo "<br>MsgChooser->setNextMessage() [[ 1 ]] no tmpfunc";
                $exe = explode( "(", $answerResponse->action, 2);
                $params = array($exe[1]);
                $tmpMsgId = call_user_func_array(array($this, $exe[0]), $params);
            }

            echo "<br>MsgChooser->setNextMessage() [[ 3 ]] Provider: ".$programId.' QSType: '.$qsType;
            echo '<br>MsgChooser->setNextMessage() Provider: '.$programId.' QSType: '.$qsType.' MsgID: '.$tmpMsgId;//die();
            $ret =  $msgCPRules->getValidAnswer($programId, $qsType, $tmpMsgId);
            echo '<br>MsgChooser->setNextMessage() return from valid answer: ';print_r($ret);

            //  get new information in case of loop
            $nextQuestionInfo  = $msgCPRules->getQuestion($tmpMsgId, $userId, 'SMS_EN', $programId, $qsType);
            echo '<br>MsgChooser->setNextMessage() nextQuestionInfo->qtype = '.$nextQuestionInfo->qtype.'';

        }

        dd('MsgChooser->setNextMessage() DONE');

        // sample array structure testing
        $serial = serialize(
            array(
                0 => array(
                    "CF_DM_HSP_10" => "1",
                    "CF_HSP_20" => "C",
                    "CF_HSP_EX_10" => "",
                ),
                1 => array(
                    "CF_RPT_50" => ""
                ),
                2 => array(
                    "CF_RPT_40" => ""
                ),
                3 => array(
                    "CF_RPT_20" => ""
                )
            )
        );
        dd($serial);

    }

        /*
        $serial = serialize(array(
            "CF_DM_HSP_10" => array(
                "Answer" => "1",
                "Response" => array(
                    "CF_HSP_20" => array(
                        "Answer" => "C",
                        "Response" => array(
                            "CF_HSP_EX_10" => array(
                                "Answer" => "",
                                "Response" => ""
                            ),
                        ),
                    ),
                ),
            ),
            "CF_RPT_40" => array(
                "Answer" => "Y",
                "Response" => ''
            )
        ));
        dd($serial);
        */

        /*
        $serial = serialize(
            array(
                0 => array(
                    "CF_DM_HSP_10" => array(
                        "Answer" => "1"),
                    "CF_HSP_20" => array(
                        "Answer" => "C"),
                    "CF_HSP_EX_10" => array(
                        "Answer" => ""),
                ),
                1 => array(
                    "CF_RPT_40" => array(
                        "Answer" => "Y")
                ),
            )
        );
        dd($serial);
        */













    // main question flow
    public function nextMessage($arrPart) {
        echo "<br>MsgChooser->nextMessage() start!";
        // locate primary key to this array
        reset($arrPart);
        $this->key 			= key($arrPart);
        $this->comment_id	= $arrPart[$this->key]['usermeta']['comment_ID'];
        $this->provid 		= $arrPart[$this->key]['usermeta']['intProgramId']; // Provider ID
        $qstype				= $arrPart[$this->key]['usermeta']['msgtype'];  // Question Group Type
        $strResponse		= urldecode($arrPart[$this->key]['usermeta']['curresp']);  // String sent to us by user.)
        $arrPart[$this->key]['usermeta']['curresp'] = $strResponse;	// put updated value back.

        // $strRespMeth 	= strtolower($arrPart[$this->key]['usermeta']['wp_7_user_config']['preferred_contact_method']).'text'; //Method for sending messages
        $strRespMeth 	= $arrPart[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_method']."_".$arrPart[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_language'];
        $this->smsMeth	= $strRespMeth;
        $arrState		= $arrPart[$this->key]['usermeta']['state'];

        $this->arrReturn 	= $arrPart; // copy of array for outside functions that are not getting array passed to them.

        // if unknow messages is recieved, send unknown message
        if(empty($this->comment_id) && !empty($strResponse)) {
            $lastMsgid = 'CF_UNSOL_UNKNOWN';
            $tmp  = $this->_ci->rules->getQuestion($lastMsgid, $this->key, $strRespMeth, $this->provid, $qstype);
            $this->storeMsg($tmp);
            error_log('Unknown message ['.$this->key.'] Sent us: '.$strResponse);
            return $this->arrReturn;
        }

        // locate msgid for last question asked
        $lastMsgid = '';
        if(!empty($arrPart[$this->key]['usermeta']['state'])) {
            end($arrPart[$this->key]['usermeta']['state']);
            $lastMsgKey =  key($arrPart[$this->key]['usermeta']['state']);
            $lastMsgid =  key($arrPart[$this->key]['usermeta']['state'][$lastMsgKey]);
        }

        // default value
        $ret = array();

        // validate the response and get info for next question
        // echo '<br>Prov: '.$this->provid.' and qstype: '.$qstype.' msg_id: '.$lastMsgid.' Response: '.$strResponse.'<br>';
        $msgCPRules = new MsgCPRules;
        $ret =  $msgCPRules->getValidAnswer($this->provid, $qstype, $lastMsgid, $strResponse);

        // if not valid, re-ask last question
        if(empty($ret) || (empty($strResponse) && $strResponse !== '0' && !empty($arrState))) {
            $tmp  = $msgCPRules->getQuestion($lastMsgid, $this->key, $strRespMeth, $this->provid, $qstype);
            if(empty($tmp)) {
                $this->End('Question not found.');
            } else {
                $testArray = $this->arrReturn[$this->key];

                // save and load invalid response message
                $tmp2  = $msgCPRules->getQuestion('CF_INV_10', $this->key, $strRespMeth, $this->provid, $qstype);
                $msgCPRules->saveResponse($testArray, $lastMsgid, $this->provid, $tmp->obs_key, 'invalid');
                $this->storeMsg($tmp2);

                // save original question again
                //$this->_ci->rules->saveResponse($testArray, $lastMsgid, $this->provid, $tmp->obs_key, 'invalid');
                // $this->storeMsg($tmp,'Invalid Response, ');
                $this->storeMsg($tmp);

                return $this->arrReturn;
            }
        }

        // update response from look up in ValidAnswer
        $arrPart[$this->key]['usermeta']['curresp'] = $this->arrReturn[$this->key]['usermeta']['curresp'] = $strResponse = $ret->value;


        // if there was a response to the last question, then save it to the comment record
        echo '<br>MsgChooser->nextMessage() last question response: '.$strResponse.'';
        if(!empty($strResponse) || $strResponse == '0') {
            $tmp  = $msgCPRules->getQuestion($lastMsgid, $this->key, $strRespMeth, $this->provid, $qstype);
            $testArray = $this->arrReturn[$this->key];
            $msgCPRules->saveResponse($testArray, $lastMsgid, $this->provid, $tmp->obs_key);
        }

        // get 1st question information
        if(empty($arrState)) {
            $tmp  = $msgCPRules->getQuestionById($ret->qid, $this->key, $strRespMeth, $this->provid, $qstype);
            echo "<br>MsgChooser->nextMessage() ret->qid=".$ret->qid;
            $this->storeMsg($tmp);
        }


        // do action
        $i = 0; // prevent infinite looping
        do {
            $tmpResponse = '';
            if(!empty($ret->answer_response)) {
                $tmpResponse = $ret->answer_response;
            }

            if(!empty($ret->action) && (!empty($arrState) or $tmp->qtype == 'None')) {
                if(strpos($ret->action, '(') === FALSE){
                    $tmpfunc = $ret->action;
                    echo "<br>MsgChooser->nextMessage() [[ 1 ]] tmpfunc = ".$tmpfunc;
                    $tmpMsgId = $this->$tmpfunc();
                    echo "<br>MsgChooser->nextMessage() [[ 2 ]] tmpMsgId = ".$tmpfunc;
                } else {
                    echo "<br>MsgChooser->nextMessage() [[ 1 ]] no tmpfunc";
                    $exe = explode( "(", $ret->action, 2);
                    $params = array($exe[1]);
                    $tmpMsgId = call_user_func_array(array($this, $exe[0]), $params);
                }

                echo "<br>MsgChooser->nextMessage() [[ 3 ]] Provider: ".$this->provid.' QSType: '.$qstype;
                //echo '<br>Provider: '.$this->provid.' QSType: '.$qstype.' MsgID: '.$tmpMsgId;//die();
                $ret =  $msgCPRules->getValidAnswer($this->provid, $qstype, $tmpMsgId);
                // echo '<br>return from valid answer: ';print_r($ret);

                //  get new information in case of loop
                $tmp  = $msgCPRules->getQuestion($tmpMsgId, $this->key, $strRespMeth, $this->provid, $qstype);

            }

            echo '<br>MsgChooser->nextMessage() tmpResponse = '. $tmpResponse;
            //print_r($ret);
            //print_r($tmp);
            //die();

            if(!empty($tmpResponse)) {
                // check for an answer response message to send
                $this->storeMsg($msgCPRules->getQuestionById($tmpResponse, $this->key, $strRespMeth, $this->provid, $qstype));
            }

            // echo '<br>After Action:';
            // echo "<br>msgid: {$tmpMsgId}  pcp: {$tmp->pcp_status}    ucp: {$tmp->ucp_status}   today: ".strpos($tmp->cdays, date('N'));

            //  make sure message maybe sent today, it not get next message
            // if($tmp->status == 'Inactive' || strpos($tmp->cdays, date('N')) === FALSE) {
            // if(($tmp->ucp_status == 'Active') || ($tmp->pcp_status == 'Active' && strpos($tmp->cdays, date('N')) !== FALSE)) {
            if(($tmp->pcp_status == 'Active') || ($tmp->ucp_status == 'Active' && strpos($tmp->cdays, date('N')) !== FALSE)) {
// echo '<br>Message may be sent today.';
            } else {
// echo '<br>Only if Message is using NextQ can not be sent today, finding next message.';

                // if(($tmp->action == 'NextQ') ){
                if(($tmp->action == 'NextQ') || (substr($tmp->action, 0, 7) == 'fxAlgor')){

// echo '<br>Getting message after '.$tmp->msg_id.'<br>';
                    $tmpMsgId = $this->NextQ($tmp->msg_id);
                    //  get new information after look up next message
                    $tmp  = $msgCPRules->getQuestion($tmpMsgId, $this->key, $strRespMeth, $this->provid, $qstype);
                }
            }
// echo "<br> After Day Check: ";
// print_r($tmp);


            if((($tmp->ucp_status == 'Active') || ($tmp->pcp_status == 'Active' )) && $tmp->qtype == 'TOD' ) {
                $msgTod = new MsgTod;
                $tmpMsgId = $msgTod->getNextTod($this->provid, $this->key);
                if(!empty($tmpMsgId)) {
                    $tmp  = $msgCPRules->getQuestion($tmpMsgId, $this->key, $strRespMeth, $this->provid, $qstype);
                    $this->storeMsg($tmp);
                }
            }

            // echo '<br>QType: '.$tmp->qtype.'<br>';
            // only store real messages, if still the TOD placeholder, then skip
            if($tmp->qtype == 'TOD') {
                $tmp->qtype = 'None';
            } else {
                $this->storeMsg($tmp);
            }
            // echo '<br>Final QType: '.$tmp->qtype.'<br>';


        } while ($tmp->qtype == 'None' and $i++ < 5);



        // if($tmp->qtype == 'None') {
        // 	// Getting Next Question
        // 	$this->NextQ();
        // }

        // if no message is found to return, send back the end signal
        if(!array_key_exists('msg_list', $this->arrReturn)) {
            $this->End('No message selected.');
        }

// echo "<pre>";		print_r($this->arrReturn);
        echo "<br>MsgChooser->nextMessage() end, return arrReturn";
        return $this->arrReturn;
        // }

        //echo "<br>-------------END nextMessage()-------------";

    }//nextMessage

    public function resendLastMsg($arrPart) {

        $qstype			= $arrPart[$this->key]['usermeta']['msgtype'];  // Question Group Type
        $strResponse	= $arrPart[$this->key]['usermeta']['curresp'];  // String sent to us by user.
        $strRespMeth 	= $arrPart[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_method']."_".$arrPart[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_language'];
        $arrState		= $arrPart[$this->key]['usermeta']['state'];

        end($arrState);
        $lastMsgid =  key($arrState);
        $tmp  = $this->_ci->rules->getQuestion($tmpMsgId, $this->key, $strRespMeth, $this->provid, $qstype);
        $this->storeMsg($tmp);

        return $this->arrReturn;
    }//resendLastMsg


    // Get Next Question
    private function NextQ($strLastMsg = '') {
        $Provider = $this->arrReturn[$this->key]['usermeta']['intProgramId'];
        $qtype 		= $this->arrReturn[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_method']."_".$this->arrReturn[$this->key]['usermeta']['wp_'.$this->provid.'_user_config']['preferred_contact_language'];
        $qstype		= $this->arrReturn[$this->key]['usermeta']['msgtype'];  // Question Group Type
        $arrState	= $this->arrReturn[$this->key]['usermeta']['state'];
        // locate msgid for last question asked
        // if(empty($arrState)){
        //var_dump($this->arrReturn);die('L256');
        if(!empty($strLastMsg)) {
            $lastMsgid = $strLastMsg;
        } elseif (!empty($this->arrReturn['msg_list'])){
            $arrSearch = $this->arrReturn['msg_list'];
            end($arrSearch);
            $lastMsgkey =  key($arrSearch);
            $lastMsgid = key($this->arrReturn['msg_list'][$lastMsgkey]);
        } else {
            die('L265');
            end($arrState);
            $lastkey =  key($arrState);
            $lastMsgid = key($arrState[$lastkey]);
        }

        // get question list
        $msgCPRules = new MsgCPRules;
        $arrQS = $msgCPRules->getNextList($Provider, $this->key, $qstype, $qtype);
        $found1 = $found2 = FALSE;
        $tmpMsgId = '';
        foreach ($arrQS as $key ) {
            // check if messages is allowed to be sent today
            if($found1 && ($key->pcp_status == 'Active' || ($key->ucp_status == 'Active' && strpos($key->cdays, date('N')) !== FALSE))){
                $found2 = TRUE;
            }

            // Send message;
            if($found2) {
                $tmpMsgId = $key->msg_id;
                break;
            }

            // find last question first
            if(($key->msg_id == $lastMsgid)) {
                // echo '<br>FOUND!!!!!';
                $found1 = TRUE;
            }

        }
        return $tmpMsgId;
    }//NextQ


    // store message information in array to be returned to calling program/function.
    private function storeMsg($arrQuestion, $extratext='') 	{
        echo "<br>MsgChooser->storeMsg() start, msg_id = $arrQuestion->msg_id, prov_id $this->provid";
        if(isset($this->arrReturn['msg_list'])) {
            if (is_object($arrQuestion) && (!$this->findkey($this->arrReturn['msg_list'], $arrQuestion->msg_id))) {
                $msgSubstitutions = new MsgSubstitutions;
                $arrQuestion->message = $msgSubstitutions->doSubstitutions($arrQuestion->message, $this->provid, $this->key);
                $this->arrReturn['msg_list'][] = array($arrQuestion->msg_id => array('qtype' => $arrQuestion->qtype, 'msg_text' => $extratext . $arrQuestion->message));
                echo "<br>MsgChooser->storeMsg() action add to msg_list (qtype => $arrQuestion->qtype, msg_text => $extratext . $arrQuestion->message)";
            }
        } else {
            if (is_object($arrQuestion)) {
                $msgSubstitutions = new MsgSubstitutions;
                $arrQuestion->message = $msgSubstitutions->doSubstitutions($arrQuestion->message, $this->provid, $this->key);
                $this->arrReturn['msg_list'][] = array($arrQuestion->msg_id => array('qtype' => $arrQuestion->qtype, 'msg_text' => $extratext . $arrQuestion->message));
                echo "<br>MsgChooser->storeMsg() action add to msg_list (qtype => $arrQuestion->qtype, msg_text => $extratext . $arrQuestion->message)";
            }
        }
        return;
    }//storeMsg


    // Jump to Question
    private function fxGoto($strNextQ) 	{
        return $this->cleanStr($strNextQ);
    }//fxGoto


    // checks if the readings message needs to be sent
    private function fxCheckForReadings($inVars) {
        // echo "<hr>In fxCheckForReadings<br>";
        $intSelect = 0;

        // parse the input parameters
        $params = explode(",", $this->cleanStr($inVars));
        // echo '<br>Params: ';
        // print_r($params);
        // echo '<hr>';

        // get list of Reading for individual
        $msgCPRules = new MsgCPRules;
        $arrList = $msgCPRules->getReadingDefaults($this->key, $this->provid);
        // echo '<br>arrList: ';
        // print_r($arrList);
        if(!empty($arrList)) {
            $arrReadings = serialize($msgCPRules->getReadings($this->provid, $this->key));
            // echo '<br>Readings found: '.$arrReadings;

            foreach ($arrList as $row) {
                // chech if biometric is active and can be sent today
                if((!empty($row->APActive) && $row->APActive == 'Active') or ($row->UActive == 'Active' and strpos($row->cdays, $row->today) !== FALSE)) {
                    if(empty($arrReadings) || strpos($arrReadings, $row->obs_key) === FALSE) {
                        $intSelect = 1;
                    }
                }
            }
        }
        // echo '<br>And intSelect is: '.$intSelect;
        // echo '<br>Returning: '.$params[$intSelect]; // debugging
        return $params[$intSelect];

    }//fxCheckForReadings




    // weight messages
    private function fxWeight($inVars) {
        $Params 		= explode(",", $this->cleanStr($inVars));
        $arrSet			= $this->_ci->rules->getLastWeight($this->provid, $this->key);
        $objTarget		= $this->_ci->rules->getTargetWeight($this->key);
        $strMsgID		= '';
        $strResponse	= urldecode($this->arrReturn[$this->key]['usermeta']['curresp']);  // String sent to us by user.)

        $boolCHF		= $this->_ci->rules->isCHF($this->key, $this->provid);
        $boolCHF		= (!empty($boolCHF) && $boolCHF == 'CHECKED' ) ? 1 : 0;


        if($boolCHF) {
            // CHF WT
            $dateLast 	= new DateTime($arrSet->obs_date);
            $dateNow 	= new DateTime();

            $intDiff	= date_diff($dateLast, $dateNow);
            // $intDiff 	= $dateLast->diff($dateNow);

            $intSelect	= 4;
            $intWtDiff	= $strResponse - $arrSet->obs_value;

            //  base calc on age of last reading
            switch ($intDiff->format('%a')) {
                case 1:
                    if($intWtDiff > 2) {
                        $intSelect = 5;
                    }
                    break;

                case 2:
                    if($intWtDiff > 4) {
                        $intSelect = 6;
                    }
                    break;

                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                    if($intWtDiff > 5) {
                        $intSelect = 7;
                    }
                    break;

                default:
                    $intSelect = 8;
                    break;
            }
        } else {
            // Regular WT based on percentage
            $intSelect	= 0;
            $intWtDiff	= 0;
            // print_r($objTarget);
            if(!empty($objTarget->meta_value) && $objTarget->meta_value > 0) {
                $intWtDiff	= 100 * $strResponse / $objTarget->meta_value - 100;
            }

            if($intWtDiff > 15) {
                $intSelect = 3;
            } elseif($intWtDiff > 5) {
                $intSelect = 2;
            } elseif($intWtDiff >= 0) {
                $intSelect = 1;
            }
        }


        // return select message from above calcs
        $strMsgID =  $Params[$intSelect];
        return $strMsgID;
    }//fxWeight


    // removes chars from string
    private function cleanStr($instr) {
        return str_replace(array('\'', '"', '(', ')'), "", $instr);
    }//cleanStr

    private function End($strError='') {
        // closes down session because some went wrong
        error_log('Msgchooser closing session due to unknown problem. '.$strError);
        $this->arrReturn['msg_list'][] = array('End' => array('qtype' => 'End', 'msg_text' => 'Thank you.'));
    } //End

    public function findKey($arrSearch, $keySearch) {
        // check if it's even an array
        if (!is_array($arrSearch)) return false;

        // key exists
        if (array_key_exists($keySearch, $arrSearch)) return true;

        // key isn't in this array, go deeper
        foreach($arrSearch as $key => $val) {
            // return true if it's found
            if ($this->findKey($val, $keySearch)) return true;
        }

        return false;
    } //findkey

    public function fxAlgorithmic($inVars) {
        // checks to see if last meds question has been asked.
        $Params = explode(",", $this->cleanStr($inVars));
        // echo '<br>Params: ';
        // print_r($Params);
        $intSelect = $sched = $y = $n = 0;

        // get counts for responses
        $arrCounts = $this->_ci->rules->getAdherenceCounts($this->provid, $this->key);
        foreach ($arrCounts as $row) {
            if($row['obs_unit'] == 'scheduled') {
                $sched = $row['count'];
            } elseif($row['obs_value'] == 'Y') {
                $y = $row['count'];
            } elseif($row['obs_value'] == 'N') {
                $n = $row['count'];
            }
        }

        echo '<br>MsgChooser->fxAlgorithmic() ';
        echo '<br>Al "Gor" Ithmic';
        echo '<br>Sched: '.$sched;
        echo '<br>Y: '.$y;
        echo '<br>N: '.$n.'<br>';

        $lastMsgid = null;
        if($sched > 0 && ($sched == ($y + $n))) {
            // last meds question has been asked
            if($sched == $y) {
                $intSelect = 0; // all Yes message
            } elseif($sched == $n) {
                $intSelect = 1; // all No message
            } else {
                $intSelect = 2; // mixed message
            }
            // get lastMsgId for NextQ
            $arrState	= $this->arrReturn[$this->key]['usermeta']['state'];
            end($arrState);
            $lastkey =  key($arrState);
            $lastMsgid = key($arrState[$lastkey]);

            echo '<br>Algorithmic Message: '.$Params[$intSelect];
            // send message
            $tmp  = $this->_ci->rules->getQuestion($Params[$intSelect], $this->key, $this->smsMeth, $this->provid);
            // echo '<br>AG Message Array:';
            // print_r($tmp);
            $this->storeMsg($tmp);

        }

        // send next message
        $rtnMsgId = $this->NextQ($lastMsgid);
        echo '<br>MsgChooser->fxAlgorithmic() Next Message: '.$rtnMsgId;
        return $rtnMsgId;
    }



}//Cpm_1_7_msgchooser_library

/* End of file cpm_1_7_msgchooser_library.php */
/* Location: ./application/librareis/cpm_1_7_msgchooser_library.php */