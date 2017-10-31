<?php namespace App\Services;

use App\Comment;
use DateTime;
use DateTimeZone;

/*
$this->load->library('curl');
$this->load->model('valuecalc_model','calc');
$this->load->model('cpm_1_7_observation_model','observation_model');
$this->load->model('cpm_1_7_comments_model','comments_model');
$this->load->database();
$this->load->library('session');
$this->load->library('cpm_1_7_substitution_library');
*/

class MsgDelivery
{

    function __construct()
    {
    }

    //returns an array with the http response headers/body from the texting service
    public function sendMessageBody($arrPart, $strMessageCode, $strMessageBody, $strCommentType = 'smsoutbound', $boolSaveState = true, $boolUnsolicited = false)
    {
        $strState = ($boolUnsolicited == false) ? 'state' : 'unsolicited';
        $intUserId = key($arrPart);
        $substitutedMessage = $strMessageBody;
        $intProgramID = $arrPart[$intUserId]['usermeta']['intProgramId'];
        $phoneNumber = $arrPart[$intUserId]['usermeta']['wp_'.$intProgramID.'_user_config']['study_phone_number'] ;

        $this->intProgramID = $intProgramID;
        $lastCommentNo = $this->writeOutboundSmsMessage($intUserId, $substitutedMessage, $strMessageCode, $strCommentType, $intProgramID);
        //Actually send itwriteOutboundSmsMessage
        $sms['phone_number'] = preg_replace('/[^0-9]/', '', $phoneNumber);
        $sms['msg_text']     = $substitutedMessage;
        $sms['msg_type']     = 'SMS';
        $sms['source']       = 'Clickatell';
        $sms['id'] = $intProgramID;
// var_export($sms);
        if ($boolSaveState) {
            //Log question to database (save the state)
            $this->saveState($intUserId, $strMessageCode, $strState, $arrPart);
        }

        return $this->sendSms($sms);
    }

    //returns an array with the http response headers/body from the texting service

    public function writeOutboundSmsMessage(
        $intUserId,
        $serialOutboundMessage,
        $strMessageId,
        $strCommentType = 'smsoutbound',
        $intProgramID = 0,
        $readingDate = 'now'
    ) {
// echo '<br>';
// var_export(func_get_args());
        // set blog id
        //$this->int_id = $intProgramID;

        //switch_to_blog( $intProgramID );
        date_default_timezone_set('America/New_York');

        $parentID = 0;
        /*
        $strCommentsTable = "wp_". $intProgramID ."_comments";
        $sql = "SELECT * FROM $strCommentsTable WHERE user_id=".$intUserId." AND comment_type like 'state%' ORDER BY comment_date DESC LIMIT 1";
        $query = DB::select( DB::raw($sql) );
        $parentID = 0;

        if(!empty($query))
        {
            $row = $query[0];
// echo "<br>Found: $row->comment_ID<br>$row->comment_content<br>";var_export($state);
            $parentID =  $row->comment_ID;
        }
        */

        $dateTime = new DateTime(urldecode($readingDate), new DateTimeZone('America/New_York'));
        $localTime = $dateTime->format('Y-m-d H:i:s');

        $dateTime->setTimezone(new DateTimeZone('UTC'));
        $gmtTime = $dateTime->format('Y-m-d H:i:s');

        $comment = new Comment;
        $comment->comment_post_ID = 0;
        $comment->comment_author = $strMessageId;
        $comment->comment_author_email = 'admin@circlelinkhealth.com';
        $comment->comment_author_url = 'https://www.circlelinkhealth.com/';
        $comment->comment_content = serialize($serialOutboundMessage);
        $comment->comment_type = $strCommentType;
        $comment->comment_parent = $parentID;
        $comment->user_id = $intUserId;
        $comment->comment_author_IP = '127.0.0.1';
        $comment->comment_agent = 'N/A';
        $comment->comment_date = $localTime;
        $comment->comment_date_gmt = $gmtTime;
        $comment->comment_approved = 1;
        $comment->save();

        /*
        $last_comment_no = DB::table('wp_'.$intProgramID.'_comments')->insertGetId( $mixCommentData );
        */
        //dd('last_comment_no ==' . $last_comment_no);
        echo '<br>MsgDelivery->writeOutboundSmsMessage() last_comment_no =' . $comment->id;
        //$last_comment_no = wp_insert_comment($mixCommentData);
        //$last_comment_no = $this->comments_model->insert_comment($mixCommentData, $this->int_id);;
//echo 'recorded';
// echo "[$last_comment_no]";
        return $comment->id;
    }

    private function saveState(
        $intUserId,
        $strMessageCode,
        $strState = 'state',
        $arrPart = null
    ) {

        /*
        // echo $strMessageCode;
        //load previous state
        $mixReturnResult = false;
        $strCommentsTable = "wp_". $this->intProgramID ."_comments";
        $sql = "SELECT * FROM $strCommentsTable WHERE user_id=$intUserId AND comment_type='state_".$arrPart[$intUserId]['usermeta']['msgtype']."' ORDER BY comment_date DESC LIMIT 1";
        $results = DB::select( DB::raw($sql) );
        */
        $commentType = $arrPart[$intUserId]['usermeta']['msgtype'];
        $results = Comment::where('user_id', '=', $intUserId)->where(
            'comment_type',
            '=',
            $commentType
        )->orderBy('comment_date', 'desc')->first();

        if (!empty($results)) {
            //echo "<br><br>UPDATE DB STATE ROW";
            echo "<br>MsgDelivery->saveState() strMessageCode=" . $strMessageCode;
            //store new question
            $row = $results[0];
            $state = unserialize($row->comment_content);
            $state[][$strMessageCode] = '';
            $comment_approved = 0;
            if (isset($arrPart['msg_list'][0][$strMessageCode]['qtype'])) {
                if (strtolower($arrPart['msg_list'][0][$strMessageCode]['qtype']) == 'end') {
                    $comment_approved = 1;
                }
            }

            echo "<br>MsgDelivery->saveState() new msg: " . $strMessageCode . "";

            // update comment, save state
            $comment = Comment::find($row->comment_ID);
            $comment->comment_content = serialize($state);
            $comment->comment_approved = $comment_approved;
            $comment->save();

            echo "<br>MsgDelivery->saveState() Update Comment#=" . $row->comment_ID;
            echo "<br>MsgDelivery->saveState() $row->comment_ID new state = " . serialize($state);

            // get sequence_id
            end($state);
            $sequence_id = key($state);

            $dateTime = new DateTime('now', new DateTimeZone('America/New_York'));
            $localTime = $dateTime->format('Y-m-d H:i:s');

            $dateTime->setTimezone(new DateTimeZone('UTC'));
            $gmtTime = $dateTime->format('Y-m-d H:i:s');

            // insert new observation
            $newObservation = new Observation;
            $newObservation->comment_id = $row->comment_ID;
            $newObservation->obs_date = $localTime;
            $newObservation->obs_date_gmt = $gmtTime;
            $newObservation->sequence_id = $sequence_id;
            $newObservation->obs_message_id = $strMessageCode;
            $newObservation->obs_method = strtoupper($arrPart[$intUserId]['usermeta']['msgtype']);
            $newObservation->user_id = $row->user_id;
            $newObservation->obs_key = '';
            $newObservation->obs_value = '';
            $newObservation->obs_unit = '';
            $newObservation->save();

            echo "<br>MsgDelivery->saveState() Created New Observation#=" . $newObservation->id;
            $log_string = "added new observation, obs_id = {$newObservation->id}" . PHP_EOL;
        }

        return;
    }

    public function sendSms($arrSMSdata)
    {
        echo "<br>MsgDelivery->sendSms() SKIPPING CURL POST, return true?";

        return true;
        //$this->curl->simple_post(OB_SMS_SERVICE, $arrSMSdata); //'http://msg.safekidney.com/msg/MsgCenter.php'

        //$arrCurlInfo = $this->curl->info;

        //return $arrCurlInfo;
    }

    //taken from Yvesh: smsoutbound_model.php

    public function sendMessage($intUserId, $strMessageCode, $strCommentType = 'smsoutbound', $boolSaveState = true, $intProgramID, $boolUnsolicited = false)
    {
        $strState = ($boolUnsolicited == false) ? 'state' : 'unsolicited';
// echo "<BR>" . $strState . "<BR>" ;
        $this->intProgramID = $intProgramID;
        //Load user metadata
        $arrusermeta = get_user_meta($intUserId, 'wp_'. $intProgramID .'_user_config', true);

        $phoneNumber = $arrusermeta['study_phone_number'];

        // $arrmessage = explode("|", $message);
        // $message = $arrmessage[1];

        //Load Message
        $message = $this->getMessageBody($strMessageCode);

        //Do Subtitutions
        $substitutedMessage = $this->doSubstitutions($message, $intUserId);
        $substitutedMessage = $this->cpm_1_7_substitution_library->doSubstitutions($message, $intProgramID, $intUserId);

        //Fire message off
        //Mark that it was sent in DB
        $lastCommentNo = $this->writeOutboundSmsMessage($intUserId, $substitutedMessage, $strMessageCode, $strCommentType, $intProgramID);
        $this->session->set_userdata('lastCommentNo', $lastCommentNo);
        //$this->setCPMstorage('lastCommentNo', $lastCommentNo, true);
        //Actually send it
        $sms['phone_number'] = preg_replace('/[^0-9]/', '', $phoneNumber);
        $sms['msg_text']     = $substitutedMessage;
        $sms['msg_type']     = 'SMS';
        $sms['source']       = 'Clickatell';
        $sms['id'] = $intProgramID;

        echo "MsgDelivery->sendMessage() [$intUserId]|[".$sms['phone_number']."]";
        echo 'MsgDelivery->sendMessage() strMessageCode= ' . $strMessageCode . ' && msg_text = ' . $sms['msg_text'] . "";
        // echo "<BR><strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/Y'>Yes</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/N'>No</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/1'>1</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/2'>2</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/3'>3</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/4'>4</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/Feel Fine'>Feel Fine</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/5'>5</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/6'>6</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/7'>7</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/8'>8</a></strong><BR>";
        // echo "<strong>If a response is needed send a <a href='cpm_1_7_smsreceive/index/" . $sms['phone_number'] . "/9'>9</a></strong><BR>";
        // echo "<strong>Start a new <a href='cpm_1_7_schedulercontroller_TST?userid=" . $intUserId . "'>Message Survey</a></strong><BR>";


        if ($boolSaveState) {
            //Log question to database (save the state)
            $this->saveState($intUserId, $strMessageCode, $strState);
        }

        // return ;
        return $this->sendSms($sms);
    }

    public function getMessageBody($strMessageCode)
    {
        $strMessageBody = '';
        $strMessageCode = str_replace('1'.'-', '', $strMessageCode);

        $strGetPostIdSql = "select meta_value content from rules_questions q
            left join rules_items i on i.qid = q.qid
            left join rules_itemmeta im on im.items_id = i.items_id AND meta_key = 'SMS_EN'
            where q.msg_id = '". $strMessageCode ."';";


        $query = $this->db->query($strGetPostIdSql);

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $strMessageBody = $row->content;
            $query->free_result();
        }
        return $strMessageBody;
    }

    public function doSubstitutions(
        $strMessage,
        $intUserId,
        $strValue = '',
        $type = ''
    ) {
    
        /**
         * @todo Finish...
         */

        if (preg_match("/#AvgLWSC#/", $strMessage)) {
            $strMessage = preg_replace('/#AvgLWSC#/', $this->calc->getAverageSteps($intUserId, 0, -7, 4), $strMessage);
        }
        if (preg_match("/#AvgSC#/", $strMessage)) {
            $strMessage = preg_replace('/#AvgSC#/', $this->calc->getAvgSC($intUserId, 4), $strMessage);
        }
        if (preg_match("/#AvgCAL#/", $strMessage)) {
            $strMessage = preg_replace('/#AvgCAL#/', $this->calc->getAvgCal($intUserId), $strMessage);
        }

        if (preg_match("/#SS_Red_Percent#/", $strMessage)) {
            $perc = $this->calc->getCigPerc($intUserId);
            $strMessage = preg_replace('/#SS_Red_Percent#/', $perc['PercReduce'] . '%', $strMessage);
        }

        /**
         *@todo get number of good BP weeks
         */
        if (preg_match("/#BP_Weeks#/", $strMessage)) {
            $strMessage = preg_replace('/#BP_Weeks#/', $this->calc->getWeeksBP($intUserId), $strMessage);
        }
        if (preg_match("/#BS_Phone#/", $strMessage)) {
            $strMessage = preg_replace('/#BS_Phone#/', $this->calc->getPhoneBS($intUserId), $strMessage);
        }
        if (preg_match("/#WT_Loss#/", $strMessage)) {
            $strMessage = preg_replace('/#WT_Loss#/', $this->calc->chkWeightLoss(14, $intUserId), $strMessage);
        }
        if (preg_match("/#SS_Urge#/", $strMessage)) {
            $strMessage = preg_replace('/#SS_Urge#/', $this->calc->getUrgeSS($intUserId), $strMessage);
        }

        if (preg_match("/#ParticipantID#/", $strMessage)) {
            $strUserName =  get_the_author_meta('display_name', $intUserId);
            $strMessage = preg_replace('/#ParticipantID#/', $strUserName, $strMessage);
        }
        if (preg_match("/#ParticipantPhone#/", $strMessage)) {
            $strUserPhone =  get_user_meta($intUserId, 'study_phone_number', true);
            $strMessage = preg_replace('/#ParticipantPhone#/', $strUserPhone, $strMessage);
        }

        if (preg_match("/#ProblemList#/", $strMessage)) {
            $arrProblemList =  get_user_meta($intUserId, 'wp_4_alert_data', true);
            if ($arrProblemList == '') {
                $arrProblemList['Problem List:'] = 'N/A';
            }
            $strMessage = preg_replace('/#ProblemList#/', 'Problem List: ' . $arrProblemList['Problem List:'], $strMessage);
        }

        if (preg_match("/#CurrentMedications#/", $strMessage)) {
            $arrCurrentMedications =  get_user_meta($intUserId, 'wp_4_alert_data', true);
            if ($arrCurrentMedications == '') {
                $arrCurrentMedications['Current Medications:'] = 'N/A';
            }
            $strMessage = preg_replace('/#CurrentMedications#/', 'Current Medications: ' . $arrCurrentMedications['Current Medications:'], $strMessage);
        }

        if (preg_match("/#SWObsLink#/", $strMessage)) {
            switch ($_SERVER['SERVER_NAME']) {
                case 'testprocessor.careplanmanager.com':
                    $serverName = 'testsw';
                    break;
                case 'stageprocessor.careplanmanager.com':
                    $serverName = 'stagesw';
                    break;
                case 'processor.careplanmanager.com':
                    $serverName = 'smartwoman';
                    break;
            }
            $urlObsLink = 'https://'. $serverName . '.careplanmanager.com/report/participant-observations/' .'?id=' . $intUserId .'&rt=' . $type;
            $strMessage = preg_replace('/#SWObsLink#/', $urlObsLink, $strMessage);
        }

        if (preg_match("/#BETAlinkPR#/", $strMessage)) {
            switch ($_SERVER['SERVER_NAME']) {
                case 'testprocessor.careplanmanager.com':
                    $serverName = 'testbeta';
                    break;
                case 'stageprocessor.careplanmanager.com':
                    $serverName = 'stagebeta';
                    break;
                case 'processor.careplanmanager.com':
                    $serverName = 'beta';
                    break;
            }
            $urlObsLink = 'https://'. $wpBlog->domain . '/manage-patients/' . $intUserId .'/progress';
            $this->load->library('tinyurl');
            $urlObsLink = $this->tinyurl->shorten($urlObsLink);
            $strMessage = preg_replace('/#BETAlinkPR#/', $urlObsLink, $strMessage);
        }

        if (preg_match("/#UserID#/", $strMessage)) {
            $strMessage = preg_replace('/#UserID#/', $intUserId, $strMessage);
        }
        if (preg_match("/#Value#/", $strMessage)) {
            $strMessage = preg_replace('/#Value#/', $strValue, $strMessage);
        }

        if (preg_match("/#EDmsgSE#/", $strMessage)) {
            $strMessage = preg_replace('/#Value#/', "#EDmsgSE# [Replacement Message Needed for this message.]", $strMessage);
        }

        if (preg_match("/#SYMPTOMS#/", $strMessage)) {
            $strCommentsTable = "wp_". $this->intProgramID ."_comments";
            $sql = "SELECT * FROM $strCommentsTable WHERE user_id=? AND comment_type in ('state', 'unsolicited') ORDER BY comment_date DESC LIMIT 1";
            $query = $this->db->query($sql, $intUserId);
            $parentID = 0;

            if ($query->num_rows > 0) {
                $row = $query->row();
                $symptoms .= '';
                $arrSymptoms = unserialize($row->comment_content);
                // echo "<br>Found: $row->comment_ID<br>";var_export($arrSymptoms) ; echo "<br>";
                foreach ($arrSymptoms as $key => $value) {
                    if ($arrSymptoms[str_replace('SE_02', 'SE_03', $key)] > 6) {
                        if (strpos($key, 'SE_02') > 0) {
                            if ($symptoms == '') {
                                $symptoms .= $value;
                            } else {
                                $symptoms .= ' and ' . $value;
                            }
                        }
                    }
                }
            }


            $strMessage = preg_replace('/#SYMPTOMS#/', $symptoms, $strMessage);
        }


        // return '[' . $intUserId.'] '.$strMessage;
        return $strMessage;
    }//saveState
}
