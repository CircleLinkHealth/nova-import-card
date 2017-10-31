<?php namespace App\Services;

use App\Comment;
use App\Observation;
use App\User;
use Date;
use DateTime;
use DateTimeZone;

/*
 *
 * $this->load->model('cpm_1_7_users_model','meta');
$this->load->model('cpm_1_7_smsdelivery_model','mailman');
$this->load->model('cpm_1_7_scheduler_model','collector');
$this->load->library('cpm_1_7_msgchooser_library');
 */

class MsgScheduler
{

    function __construct()
    {
    }

    public function test($blogId)
    {
        $msgUser = new MsgUser;
        $activeUsers = $msgUser->get_all_active_patients($blogId);
        //dd($activeUsers);
        foreach ($activeUsers as $key => $intUserID) {
            if ($msgUser->check_for_scheduled_records($intUserID, $blogId)) {
                $arrPart = $msgUser->get_users_data($intUserID, 'id', $blogId, true);
                //$ret = $this->create_app_schedule($arrPart);
            }
        }
    }


    /**
     * @param $intProgramID
     */
    public function sendDailyReminder($intProgramID, $debug = false)
    {
        date_default_timezone_set('America/New_York');
        //switch_to_blog( $intProgramID );
        echo "<br><br>#################### start sendDailyReminder() ######################";
        $today = date('N');
        echo "<br><br>sendDailyReminder Date: ".$today."<br><br>";

        if (in_array($today, ["1","3","5"])) {
            $reminders = ['daily' => 'day', 'welcome' => 'new' ,'hospital' => 'hsp_dm'];
        } else {
            $reminders = ['daily' => 'day', 'welcome' => 'new' ];
        }
        foreach ($reminders as $msgType => $msgTypeAbrev) {
            echo "<br><br>^^^^^^^^ START msgType = $msgType ^^^^^^^^<br>";
            $arrMsgType = ['msgType' => $msgType, 'msgTypeAbrev' => $msgTypeAbrev];
            $msgUser = new MsgUser;
            $arrUsers = $msgUser->get_readyusers_for_daily_reminder($intProgramID, $arrMsgType, null, 1); //, $strDevice, $strDate);

            // loop through each ready user
            echo "<br><br>MsgScheduler->sendDailyReminder() # of users to process = " . count($arrUsers);
            foreach ($arrUsers as $key => $value) {
                // default
                $tc[$value['user_id']] = [
                    'contactDays' => '',
                    'status' => ''
                ];
                echo '<br><br>[---Process User #'.$value['user_id'].'---]<br>';
                $arrPart[$value['user_id']] = $msgUser->get_users_data($value['user_id'], 'id', $intProgramID);
                //Added to check for Transitional Care Active and contact day
                $ucp = $msgUser->get_user_care_plan_items($value['user_id'], $intProgramID);
                foreach ($ucp as $ucpkey => $ucpvalue) {
                    if ($ucpvalue['section_text'] == 'Transitional Care Management'
                        && $ucpvalue['items_text'] == 'Contact Days') {
                        $tc[$value['user_id']]['contactDays'] = $ucpvalue['meta_value'];
                    }

                    if ($ucpvalue['section_text'] == 'Transitional Care Management'
                        && $ucpvalue['meta_key'] == 'status') {
                        $tc[$value['user_id']]['status'] = $ucpvalue['meta_value'];
                    }
                }
                if ($debug) {
                    var_export($tc);
                }
                if ($msgType == "hospital"
                    && in_array($today, explode(',', $tc[$value['user_id']]['contactDays']))
                    && $tc[$value['user_id']]['status'] == 'Active' ) {
                    echo "[".$value['user_id']."]UCP ALLOWED SEND $msgType<BR>";
                } elseif ($msgType == 'hospital') {
                    echo "[".$value['user_id']."]UCP STOPPED SEND $msgType<BR>";
                    continue ;
                } else {
                    echo "MsgScheduler->sendDailyReminder() l90 msgtype checkpoint";
                }
                // End Transitional Care Check


                $arrPart[$value['user_id']][$value['user_id']]['usermeta']['msgtype'] = $msgTypeAbrev;
                $msgChooser = new MsgChooser;
                $nextMessageInfo = $msgChooser->nextMessage($arrPart[$value['user_id']]);

                if ($msgType == 'welcome') {
                    $content_author = 'welcome';
                } else {
                    $content_author = 'dailyreminder';
                }

                //  Create new state comment
                if (!$debug) {
                    $comment_id = $this->addStateComment($intProgramID, 'state_'.$msgTypeAbrev, $value['user_id'], 'dailyreminder');
                }
                $return[$value['user_id']]['usermeta']['comment_ID'] = $comment_id;

                // loop through the message list and send each message
                foreach ($nextMessageInfo['msg_list'] as $msg => $msgResponse) {
                    if ($debug) {
                        var_export($msgResponse);
                    }
                    foreach ($msgResponse as $msgId => $msgMeta) {
                        if (!empty($msgId)) {
                            $msgDelivery = new MsgDelivery;
                            if (!$debug) {
                                echo "<br>MsgScheduler->sendDailyReminder() -> msgDelivery->sendMessageBody()";
                                $sendresult = $msgDelivery->sendMessageBody($nextMessageInfo, $msgId, $msgMeta['msg_text'], 'smsoutbound', true);
                                error_log("Sent Msg: $msg to user " . $value['user_id'] . " with no delay.");
                            }
                        }
                    }
                }
            }
            echo "<br>MsgScheduler->sendDailyReminder() done processing users for = " . $msgType . "<br><br>";
        }
        echo "#################### end sendDailyReminder() ######################<br><br>";
    }



    private function addStateComment($programId, $comment_type, $user_id, $comment_author, $arrCommentContent = [])
    {
        $dateTime = new DateTime('now', new DateTimeZone('America/New_York'));
        $localTime = $dateTime->format('Y-m-d H:i:s');

        $dateTime->setTimezone(new DateTimeZone('UTC'));
        $gmtTime = $dateTime->format('Y-m-d H:i:s');

        $comment = new Comment;
        $comment->comment_post_ID = 0;
        $comment->comment_author = $comment_author;
        $comment->comment_author_email = 'admin@circlelinkhealth.com';
        $comment->comment_author_url = 'https://www.circlelinkhealth.com/';
        $comment->comment_content = serialize($arrCommentContent);
        $comment->comment_type = $comment_type;
        $comment->comment_parent = '1';
        $comment->user_id = $user_id;
        $comment->comment_author_IP = '127.0.0.1';
        $comment->comment_agent = 'N/A';
        $comment->comment_date = $localTime;
        $comment->comment_date_gmt = $gmtTime;
        $comment->comment_approved = 1;
        $comment->save();
        //echo "<br>MsgScheduler->addStateComment() Created New Comment#=" . $comment->id;
        return $comment->id;
    }



    public function index($intProgramID = null, $strDevice = null, $strDate = null)
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
        //$ret = $this->sendDailyReminder($intProgramID);

        // create Scheduled messages if they don't already exist
        $ret = $this->createScheduledMessages($intProgramID);

        die('<br>index() finished, end');
    }


    function createScheduledMessages($intProgramID)
    {

        echo "<br><br>#################### start createScheduledMessages() ######################<br>";

        $msgUser = new MsgUser;
        $active_users = $msgUser->get_all_active_patients($intProgramID);

        if (empty($active_users)) {
            echo "<br>createScheduledMessages() -> No patients found to process";
        } else {
            foreach ($active_users as $key => $intUserID) {
                if (!$msgUser->check_for_scheduled_records($intUserID, $intProgramID)) {
                    echo "<br>createScheduledMessages() -> Patient $intUserID - Already processed";
                } else {
                    echo "<br>createScheduledMessages() -> Patient $intUserID - Ready to process:";
                    $arrPart[$intUserID] = $msgUser->get_users_data($intUserID, 'id', $intProgramID, true);
                    $arrPart[$intUserID][$intUserID]['usermeta']['msgtype'] = 'SOL';
                    $ret = $this->create_app_schedule($arrPart[$intUserID]);
                }
            }
        }

        // instantiate arrays
        $arrUsers = [];
        $arrPart = [];

        /*
        // get ready users
        $arrUsers = $msgUser->get_readyusers($intProgramID,null); //, $strDevice, $strDate);
        // echo "<br>Ready Users:<pre>";var_export($arrUsers);echo "</pre><br>";
        echo "<br><br>MsgScheduler->createScheduledMessages() # of users to process = " . count($arrUsers);
        if (!empty($arrUsers))
        {
            foreach ($arrUsers as $key => $value)
            {
                //echo "<BR><pre>". $key."|";
                echo '<br><br>[---Process User #'.$value['user_id'].'---]<br>';

                $wpUser = User::find($value['user_id']);
                $userMeta = $wpUser->userMeta();
                if(empty($userMeta['user_config'])) {
                    echo "<br>MsgScheduler->createScheduledMessages() Skip, Missing User Config";
                    continue 1;
                }
                if(!$wpUser->primaryProgramId()) {
                    echo "<br>MsgScheduler->createScheduledMessages() Skip, Missing ProgramId";
                    continue 1;
                }

                $arrPart[$value['user_id']] = $msgUser->get_users_data($value['user_id'], 'id', $intProgramID);
                $arrPart[$value['user_id']][$value['user_id']]['usermeta']['msgtype'] = 'SOL'; // hardcoded SOL

                // get next msg data
                $msgChooser = new MsgChooser;
                $return = $msgChooser->nextMessage($arrPart[$value['user_id']]);

                //  Create new state comment
                $comment_id = $this->addStateComment($intProgramID, 'state_sol', $value['user_id'], 'smsdelivery_model');
                $return[$value['user_id']]['usermeta']['comment_ID'] = $comment_id;

                // record what messages should be sent today.
                if ($msgUser->check_for_scheduled_records($value['user_id'], $intProgramID)){
                    // $this->create_app_schedule($arrPart[$value['user_id']]);
                }

                // calculate delay
                if (count($return)>1) {$delay = count($return)*3;} else {$delay = 0;}

                // loop through messages
                foreach ($return['msg_list'] as $msg => $resp)
                {
                    foreach ($resp as $msg => $meta) {
                        echo "<br>MsgScheduler->createScheduledMessages() $msg == ".$meta['msg_text']."";
                        // $sendresult = $this->mailman->sendMessage($value['user_id'],$msg,'smsoutbound',true, $intProgramID);
                        $msgDelivery = new MsgDelivery;
                        $sendresult = $msgDelivery->sendMessageBody($return,$msg,$meta['msg_text'],'smsoutbound',true);
                        $delay = abs($delay-2);

                        sleep($delay);
                        error_log("Sent Msg: $msg with a $delay second delay.");
                    }
                    // send $return to comments DB to log user state
                }
            }
        }
        */
        echo "<br><br>#################### end createScheduledMessages() ######################";
    }

    public function create_app_schedule($arrData)
    {
        reset($arrData);
        $user_id        =  key($arrData);
        echo "<br><br>MsgScheduler->create_app_schedule() Start";
        $wpUser = User::find($user_id);
        $userMeta = $wpUser->userMeta();
        if (empty($userMeta['user_config'])) {
            echo "<br>MsgScheduler->create_app_schedule() Missing User Config";
            return false;
        }
        if (!$wpUser->program_id) {
            echo "<br>MsgScheduler->create_app_schedule() Missing ProgramId";
            return false;
        }
        $provider_id = $arrData[$user_id]['usermeta']['intProgramId']; // Provider id
        $qstype         = $arrData[$user_id]['usermeta']['msgtype'];  // Question Group Type
        $qtype          = $arrData[$user_id]['usermeta']['wp_'.$provider_id.'_user_config']['preferred_contact_method']."_".$arrData[$user_id]['usermeta']['wp_'.$provider_id.'_user_config']['preferred_contact_language'];
        $strMessageId   = 'schedulercontroller';

        // get question list
        echo "<br><br>MsgScheduler->create_app_schedule() $provider_id, $user_id, $qstype, $qtype";
        $msgCPRules = new MsgCPRules;
        $arrQS = $msgCPRules->getNextList($provider_id, $user_id, $qstype, $qtype);
        $tmpArr = [];
        $appArr = [];
        $i = 0;
        foreach ($arrQS as $key) {
            // check if messages is allowed to be sent today
            if (($key->pcp_status == 'Active' || ($key->ucp_status == 'Active' && strpos($key->cdays, date('N')) !== false))) {
                // $tmpArr[$i++][$key['msg_id']] = '';
                $tmpArr[($i+1)][$key->msg_id] = $key->obs_key;
                $appArr[$i][0] = [$key->msg_id => ""];
                $i++;
            }
        }
        // $serialOutboundMessage = serialize($tmpArr);

        // write out scheduled comment
        $msgDelivery = new MsgDelivery;
        //$lastkey = $msgDelivery->writeOutboundSmsMessage($user_id,$tmpArr,$strMessageId, 'scheduled',$provider_id);

        // write out state_app comment
        //$lastkey = $msgDelivery->writeOutboundSmsMessage($user_id,$appArr,$strMessageId, 'state_app',$provider_id);

        // write out observation records
        foreach ($tmpArr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $observation = new Observation;
                $observation->comment_id = 0;
                $observation->sequence_id = $key;
                $observation->user_id = $user_id;
                $observation->obs_message_id = $key2;
                $observation->obs_method = $qstype;
                $observation->obs_key = $value2;
                $observation->obs_unit = 'scheduled';
                $observation->obs_date = date("Y-m-d H:i:s");
                $observation->obs_date_gmt = gmdate("Y-m-d H:i:s");
                $observation->save();
                echo "<br>MsgScheduler->create_app_schedule() Added Observation obs_id#=" . $observation->id;
            }
        }

        echo "<br>MsgScheduler->create_app_schedule() End";
        return 0;
    }

    function triggerUrl($strUrl)
    {
        // echo "Calling: $strUrl<br>";
        $this->curl->simple_get($strUrl);
        echo "Called: $strUrl<BR>";

        return 'OK';
    }
}
