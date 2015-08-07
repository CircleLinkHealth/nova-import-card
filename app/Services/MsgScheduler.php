<?php namespace App\Services;

use App\WpUser;
use App\WpUserMeta;
use App\Services\MsgUser;
/*
 *
 * $this->load->model('cpm_1_7_users_model','meta');
$this->load->model('cpm_1_7_smsdelivery_model','mailman');
$this->load->model('cpm_1_7_scheduler_model','collector');
$this->load->library('cpm_1_7_msgchooser_library');
 */

class MsgScheduler {

    function __construct()
    {
    }

    public function test($blogId){
        $msgUser = new MsgUser;
        $activeUsers = $msgUser->get_all_active_users($blogId);
        //dd($activeUsers);
        foreach ($activeUsers as $key => $intUserID) {
            if ($msgUser->check_for_scheduled_records($intUserID, $blogId)){
                $arrPart = $msgUser->get_users_data($intUserID, 'id', $blogId, true);
                //$ret = $this->scheduledSMS($arrPart);
            }
        }
    }


    /**
     * @param $intProgramID
     */
    public function sendDailyReminder($intProgramID, $debug=false){
        date_default_timezone_set('America/New_York');
        //switch_to_blog( $intProgramID );
        $today = date('N');
        echo "<pre>sendDailyReminder Date: ".$today;

        if (in_array($today, array("1","3","5") ) ) {
            $reminders = array('daily' => 'day', 'welcome' => 'new' ,'hospital' => 'hsp_dm');
        } else {
            $reminders = array('daily' => 'day', 'welcome' => 'new' );
        }

        foreach ($reminders as $msgType => $msgTypeAbrev) {
            echo '<br>---------------<br>DAILY REMINDER TYPE: '.$msgType.'<br>---------------<br>';
            $arrMsgType = array('msgType' => $msgType, 'msgTypeAbrev' => $msgTypeAbrev);
            $msgUser = new MsgUser;
            $arrUsers = $msgUser->get_readyusers_for_daily_reminder($intProgramID, $arrMsgType, null, 1); //, $strDevice, $strDate);

            // loop through each ready user
            foreach ($arrUsers as $key => $value) {
                $arrPart[$value['user_id']] = $this->meta->get_users_data($value['user_id'], 'id', $intProgramID);
                //Added to check for Transitional Care Active and contact day
                $ucp = $this->meta->get_user_care_plan_items($value['user_id'], $intProgramID);
                foreach ($ucp as $ucpkey => $ucpvalue) {
                    if ($ucpvalue['section_text'] == 'Transitional Care Management'
                        && $ucpvalue['items_text'] == 'Contact Days')
                    {
                        $tc[$value['user_id']]['contactDays'] = $ucpvalue['meta_value'];
                    }

                    if ($ucpvalue['section_text'] == 'Transitional Care Management'
                        && $ucpvalue['meta_key'] == 'status')
                    {
                        $tc[$value['user_id']]['status'] = $ucpvalue['meta_value'];
                    }
                }
                if($debug) var_export($tc);
                if( $msgType == "hospital"
                    && in_array($today, explode(',', $tc[$value['user_id']]['contactDays']))
                    && $tc[$value['user_id']]['status'] == 'Active' )
                { echo "[".$value['user_id']."]UCP ALLOWED SEND $msgType<BR>";}
                elseif ($msgType == 'hospital')
                {
                    echo "[".$value['user_id']."]UCP STOPPED SEND $msgType<BR>";
                    continue ;
                }
                // End Transitional Care Check


                $arrPart[$value['user_id']][$value['user_id']]['usermeta']['msgtype'] = $msgTypeAbrev;
                $nextMessageInfo = $this->cpm_1_7_msgchooser_library->nextMessage($arrPart[$value['user_id']]);

                if($msgType == 'welcome') {
                    $content_author = 'welcome';
                } else {
                    $content_author = 'dailyreminder';
                }

                //  Create new state comment
                if(!$debug)                $comment_id = $this->addStateComment($intProgramID, 'state_'.$msgTypeAbrev, $value['user_id'], 'dailyreminder');
                $return[$value['user_id']]['usermeta']['comment_ID'] = $comment_id;

                // loop through the message list and send each message
                foreach ($nextMessageInfo['msg_list'] as $msg => $msgResponse)
                {
                    if($debug) var_export($msgResponse);
                    foreach ($msgResponse as $msgId => $msgMeta) {
                        if(!empty($msgId)) {
                            if(!$debug)                 $sendresult = $this->mailman->sendMessageBody($nextMessageInfo,$msgId,$msgMeta['msg_text'],'smsoutbound',true);
                            if(!$debug)                 error_log("Sent Msg: $msg to user ".$value['user_id']." with no delay.");
                        }
                    }
                }
            }
        }
        echo "</pre><br>";
        // die();
    }



    private function addStateComment($intProgramID, $comment_type, $user_id, $comment_author,$arrCommentContent = array()) {
        date_default_timezone_set('America/New_York');
        switch_to_blog( $intProgramID );

        $comment_id = wp_insert_comment(array(
            'comment_author' => $comment_author,
            'comment_author_email' => 'admin@circlelinkhealth.com',
            'comment_author_url' => 'http://circlelinkhealth.com/',
            'comment_content' => serialize($arrCommentContent),
            'comment_type' => $comment_type,
            'comment_parent' => 1,
            'user_id' => $user_id,
            'comment_author_IP' => '127.0.0.1',
            'comment_agent' => '',
            'comment_date' => date('Y-m-d H:i:s'),
            'comment_approved' => 0
        ));

        return $comment_id;
    }



    public function index($intProgramID=null, $strDevice=null, $strDate=null)
    {
        echo "<pre><span class='alert'>Running scheduler</span><span>: " . date("Y-m-d") . " on site: |[" . $intProgramID . "]|\n<BR></span>";
        echo "<span class='alert2'>@: " . date("Y-m-d H:i:s T") . "<BR></span>";

        date_default_timezone_set('America/New_York');
        //switch_to_blog( $intProgramID );

        // VALIDATION
        // ip blocker
        $ip = '66.249.88.';
        if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($ip)) === $ip) {
            // deny access
        }

        // exit if no programID
        if ($intProgramID == null) {
            $html = "missing program id, exit";
            exit();
        }

        // send daily reminder
        $ret = $this->sendDailyReminder($intProgramID);

        // create Scheduled messages if they don't already exist
        $active_users = $this->meta->get_all_active_users($intProgramID);
        foreach ($active_users as $key => $intUserID) {
            if ($this->meta->check_for_scheduled_records($arrPart, $intUserID, $intProgramID)){
                $arrPart[$intUserID] = $this->meta->get_users_data($intUserID, 'id', $intProgramID, true);
                $arrPart[$intUserID][$intUserID]['usermeta']['msgtype'] = 'SOL';
                $ret = $this->scheduledSMS($arrPart[$intUserID]);
                $arrCommentContent = $this->meta->get_user_state_record_by_id($intProgramID,$ret);
                $comment_content = unserialize($arrCommentContent->comment_content);
                $retState = $this->addStateComment($intProgramID, 'state_app', $intUserID, 'schedulercontroller',$comment_content);
            }
        }

        // intstantiate arrays
        $arrUsers = array();
        $arrPart = array();

        // get ready users
        $arrUsers = $this->collector->get_readyusers($intProgramID,null); //, $strDevice, $strDate);
        // echo "<br>Ready Users:<pre>";var_export($arrUsers);echo "</pre><br>";
        if (!empty($arrUsers))
        {
            foreach ($arrUsers as $key => $value)
            {
                //echo "<BR><pre>". $key."|";
                echo "<BR>Processing [".$value['user_id']."]<BR>";

                $arrPart[$value['user_id']] = $this->meta->get_users_data($value['user_id'], 'id', $intProgramID);
                $arrPart[$value['user_id']][$value['user_id']]['usermeta']['msgtype'] = 'SOL'; // hardcoded SOL

                // get next msg data
                $return = $this->cpm_1_7_msgchooser_library->nextMessage($arrPart[$value['user_id']]);

                //  Create new state comment
                $comment_id = $this->addStateComment($intProgramID, 'state_sol', $value['user_id'], 'smsdelivery_model');
                $return[$value['user_id']]['usermeta']['comment_ID'] = $comment_id;

                // record what messages should be sent today.
                if ($this->meta->check_for_scheduled_records($arrPart, $value['user_id'], $intProgramID)){
                    // $this->scheduledSMS($arrPart[$value['user_id']]);
                }

                // calculate delay
                if (count($return)>1) {$delay = count($return)*3;} else {$delay = 0;}

                // loop through messages
                foreach ($return['msg_list'] as $msg => $resp)
                {
                    foreach ($resp as $msg => $meta) {
                        echo "<BR>$msg == ".$meta['msg_text']."<BR>";
                        // $sendresult = $this->mailman->sendMessage($value['user_id'],$msg,'smsoutbound',true, $intProgramID);
                        $sendresult = $this->mailman->sendMessageBody($return,$msg,$meta['msg_text'],'smsoutbound',true);
                        $delay = abs($delay-2);

                        sleep($delay);
                        error_log("Sent Msg: $msg with a $delay second delay.");
                    }
                    // send $return to comments DB to log user state
                }
            }
        }
    }

    function triggerUrl($strUrl)
    {
        // echo "Calling: $strUrl<br>";
        $this->curl->simple_get($strUrl);
        echo "Called: $strUrl<BR>";
        return 'OK';

    }


    public function scheduledSMS($arrData){

        reset($arrData);
        $user_id        =  key($arrData);
        $provider_id    = $arrData[$user_id]['usermeta']['intProgramId']; // Provider ID
        $qstype         = $arrData[$user_id]['usermeta']['msgtype'];  // Question Group Type
        $qtype          = $arrData[$user_id]['usermeta']['wp_'.$provider_id.'_user_config']['preferred_contact_method']."_".$arrData[$user_id]['usermeta']['wp_'.$provider_id.'_user_config']['preferred_contact_language'];
        $strMessageId   = 'schedulercontroller';

        // get question list
        echo "$provider_id, $user_id, $qstype, $qtype";
        $arrQS = $this->rules->getNextList($provider_id, $user_id, $qstype, $qtype);
        $tmpArr = array();
        $i = 0;
        foreach ($arrQS as $key ) {
            // check if messages is allowed to be sent today
            if(($key['pcp_status'] == 'Active' || ($key['ucp_status'] == 'Active' && strpos($key['cdays'], date('N')) !== FALSE))){
                // $tmpArr[$i++][$key['msg_id']] = '';
                $tmpArr[$i++][$key['msg_id']] = $key['obs_key'];
            }
        }
        // $serialOutboundMessage = serialize($tmpArr);

        // write out commecomment_IDnt record
        $lastkey = $this->mailman->writeOutboundSmsMessage($user_id,$tmpArr,$strMessageId, 'scheduled',$provider_id);

        // write out observation records
        foreach ($tmpArr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                // echo '<br>'.$key.': '.$key2.' -> '.$value2;
                //flag obs_processor of response
                $data = array(
                    'comment_id' => $lastkey,
                    'sequence_id' => $key,
                    'user_id' => $user_id,
                    'obs_message_id' => $key2,
                    'obs_method' => $qstype,
                    'obs_key' => $value2,
                    'obs_unit' => 'scheduled'
                );

                // insert new observation record
                $obs_id = $this->obs->insert_observation($data, false, $provider_id);

            }
        }
        // echo "</pre>";
        return $lastkey;
    }



}