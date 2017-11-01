<?php namespace App\Services;

/*
$this->load->library('cpm_1_7_msgchooser_library');
        $this->load->model('cpm_1_7_users_model','users_model');
        $this->load->model('cpm_1_7_smsdelivery_model','mailman');
        $this->load->model('cpm_1_7_altanswers_model','answers');
        $this->load->model('cpm_1_7_rules_model','rules');
*/

class MsgReceiver
{

    function __construct()
    {
    }

    public function index($phone, $response, $msgID = null)
    {
        //This is how we test it.
        $this->getInboundStream(7, $msgID, $phone, $response);
    }

    public function getInboundStream($intBlogId, $hexMoMsgId, $strPhoneNumber, $strResponseMessage)
    {
        $strResponseMessage = str_replace('%20', ' ', $strResponseMessage);
        $ip = '66.249.88.';
        if (substr($_SERVER['REMOTE_ADDR'], 0, strlen($ip)) === $ip) {
            // deny access
        }
        date_default_timezone_set('America/New_York');
        $strCommentsTable = 'wp_' . $intBlogId . '_comments';
        $boolUnsolicited = false;
        $boolSkip = false;


        echo "<br>MsgReceiver->getInboundStream() <br><pre>Response Data:$intBlogId ". $_SERVER['SERVER_NAME'];
        var_export($strResponseMessage);
        error_log("Response Data: ".$strResponseMessage);
// echo "</pre><br>";
        echo "<br>";
        $sql = "SELECT * FROM ma_". $intBlogId ."_outbound_log WHERE phone_no=1$strPhoneNumber AND date(call_logged) = date(now()) AND info rLIKE 'Inbound SMS' AND info LIKE '%$hexMoMsgId%' ;";
        $query = $this->db->query($sql, array($strPhoneNumber, $hexMoMsgId));

        if ($query->num_rows() >= 2) {
            $row   = $query->row();
            error_log("Duplicate Message Response from Clickatell!!!!!!! wp_" . $intBlogId . "_outbound_log: " . $row->id . " $hexMoMsgId $sql");
            exit();
        }


        $studyCutOffTime = "23:59"; // Pull this from wp_X_postmeta 'study_cut_off_time' postid = 41

        $sql = "SELECT * FROM wp_". $intBlogId ."_postmeta WHERE meta_key=?";
        $query = $this->db->query($sql, 'study_cut_off_time');

        if ($query->num_rows() > 0) {
            $row   = $query->row();
            error_log('studyCutOffTime: ' . $row->meta_value);
            $studyCutOffTime = $row->meta_value;
        }
        error_log("SMS In from: " . $strPhoneNumber. ' Msg: ' . $strResponseMessage . ' on Blog: ' . $intBlogId, 0);

        $arrPart = $this->users_model->get_users_data($strPhoneNumber, 'phone', $intBlogId);
        if (empty($arrPart)) {
            $this->sendInvalid($intBlogId, $strPhoneNumber);
        }
        $intUserId = key($arrPart);
        $arrPart[$intUserId]['usermeta']['curresp'] = trim($strResponseMessage) ;

        $arrPart = $this->users_model->userSmsState($arrPart);
        $preferred_contact_time = strtotime(str_replace(" ET", "", $arrPart[$intUserId]['usermeta']['wp_'. $intBlogId .'_user_config']['preferred_contact_time']));

        switch ($arrPart[$intUserId]['usermeta']['msgtype']) {
            // @todo This should bo into a DB flag
            case 'SOL':
                $boolUnsolicited = false;
                break;

            default:
                $boolUnsolicited = true;
                break;
        }
        //call the chooser

// echo "Last State: $msgtype<BR>";
        if (date("H:i") > date("H:i", strtotime($studyCutOffTime))) {
            error_log("1 Cutoff: TRUE:" .date("H:i") .' > '. date("H:i", strtotime($studyCutOffTime)));
        }
        if (date("H:i") < date("H:i", $preferred_contact_time)) {
            error_log("2 Before Pref Contact Time: TRUE | ". date("H:i") . " < " .date("H:i", $preferred_contact_time));
        } else {
            error_log(date("H:i") . " > " .date("H:i", $preferred_contact_time));
        }
        if ($boolUnsolicited==false) {
            error_log("3 Unsolicited: false");
        } else {
            error_log("3 Unsolicited: TRUE");
        }
        // exit();

// echo "<pre>";var_export($arrPart);echo "</pre><br>";
// exit();

        if ((date("H:i") > date("H:i", strtotime($studyCutOffTime))
                or (date("H:i") < date("H:i", $preferred_contact_time)) or $boolSkip == true)
            && $boolUnsolicited==false
        ) {
            // need to stop messaging after 23:55 and until the users contact time.

            // echo "<pre>State: ". date("h:i A T"); var_dump($arrPart);echo "</pre><br>";
            echo "<br>MsgReceiver->getInboundStream() Messaging Finfished for the day...";
            error_log("Messaging Finfished for the day @ ".$studyCutOffTime."... User: " . $intUserId);

            exit("Need Exit Routine");
            $sendresult = $this->mailman->sendMessage($intUserId, 'BT_EX_01', 'sms_session_over', false, $intBlogId, $boolUnsolicited);
        }

// echo "To MSG_Chooser: <pre>";var_export($arrPart);echo "</pre>";
        $return = $this->cpm_1_7_msgchooser_library->nextMessage($arrPart, $boolUnsolicited);
// echo "From MSG_Chooser: <pre>";var_export($return);echo "</pre>";
// exit();

        if ($return == null) {
            exit("No Messaging Found from Message Chooser...");
        }
// echo "From MSG_Chooser: <pre>";var_export($return);echo "</pre>";exit();


        if (count($return['msg_list'])>1) {
            $delay = count($return['msg_list'])*3;
        } else {
            $delay = 0;
        }

        foreach ($return['msg_list'] as $msg => $resp) {
            foreach ($resp as $msg => $meta) {
                echo "<br>MsgReceiver->getInboundStream() $msg == ".$meta['msg_text']."<BR>";
                if (!$msg == '') {
                    $sendresult = $this->mailman->sendMessageBody($return, $msg, $meta['msg_text'], 'smsoutbound', true);
                }
                error_log("Sent Msg: $msg with a $delay second delay.");
                $delay = abs($delay-2);

                if (strtolower($meta['qtype']) == 'end') {
                    $this->resendLastMsg($return);
                }
            }
            sleep($delay);

            // send $return to comments DB to log user state
        }
    }

    public function sendInvalid(
        $intBlogId,
        $strPhoneNumber
    ) {
        $tmp = $this->rules->getQuestion('CF_INV_30');
        $msg = $this->mailman->doSubstitutions($tmp->message, 0);
        $sms['phone_number'] = preg_replace('/[^0-9]/', '', $strPhoneNumber);
        $sms['msg_text'] = $msg;
        $sms['msg_type'] = 'SMS';
        $sms['source'] = 'Clickatell';
        $sms['id'] = $intBlogId;

        if (!$msg == '') {
            $sendresult = $this->mailman->sendSMS($sms);
        }

        exit("No User Found...$strPhoneNumber...$intBlogId....$msg");
    }

    private function resendLastMsg($arrPart)
    {
        echo "<br>MsgReceiver->resendLastMsg() resendLastMsg:<pre>";
        // var_export($arrPart);
    }

    private function deduceBlogIdFromApiId($strApiId)
    {
        $intBlogId = 0;

        switch ($strApiId) {
            case '14153495301':
                $intBlogId = 5;
                break;
        }

        return $intBlogId;
    }

    private function sanitizeResponse($strResponse)
    {
        $strResponse = preg_replace('/[^0-9a-zA-Z ]/', '', urldecode($strResponse));

        return $strResponse;
    }
}
