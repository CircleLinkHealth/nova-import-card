<?php namespace App\Services;

use App\User;
use App\UserMeta;
use DateTime;
use DateTimeZone;
use DB;

class MsgUser
{


    public function get_all_active_patients($id)
    {
        // get all users
        $allUsers = $this->get_all_users($id);
        if (!$allUsers->isEmpty()) {
            $activeUsers = [];
            foreach ($allUsers as $user) {
                // check if active based on user_config
                $user_config = unserialize($user['user_config']);
                if (strtolower($user_config['status']) == 'active') {
                    $activeUsers[] = $user['id'];
                }
            }
            if (empty($activeUsers)) {
                $activeUsers = false;
            }
            return $activeUsers;
        } else {
            return false;
        }
    }

    public function get_all_users($id)
    {
        // set blog id
        $this->int_id = $id;

        $query = User::select('wp_users.*', 'um.meta_value AS user_config');
        $query->join('wp_usermeta AS um', function ($join) use
            (
            $id
        ) {
            $join->on('wp_users.id', '=', 'um.user_id')->where('um.meta_key', '=', 'wp_' . $id . '_user_config');
        });
        $query->whereHas('roles', function ($q) {
            $q->where('name', '=', 'participant');
        });
        $query->orderBy("id", "desc");
        $allUsers = $query->get();

        return $allUsers;
    }

    public function check_for_scheduled_records($userId, $blogId)
    {
        $query = DB::connection('mysql_no_prefix')->table('wp_'. $blogId .'_comments');
        $query->select('*');
        $query->where('user_id', '=', $userId);
        $query->where('comment_author', '=', 'schedulercontroller');
        $query->where('comment_type', '=', 'scheduled');
        $query->whereRaw('DATE(comment_date) = DATE(now())');
        $recordExists = $query->first();
        if ($recordExists) {
            //echo "<br>MsgUser->check_for_scheduled_records() [$userId] scheduled records found, return false";
            return false;
        } else {
            //echo "<br>MsgUser->check_for_scheduled_records() [$userId] no scheduled records, return true";
            return true;
        }
    }


    public function get_users_data($strUserKey, $strKeyType = 'id', $intBlogId = null, $includeUCP = false)
    {
        $arrReturnResult = [];

        switch ($strKeyType) {
            default:
            case 'id':
                $intUserId = $strUserKey;
                $arrReturnResult = $this->standard_user_lookup([$intUserId], $intBlogId, $includeUCP);
                break;

            /*
			case 'phone' :
				$strUserKey = preg_replace('/[^0-9]/', '', $strUserKey);

				if($intBlogId === null)
				{
					$intBlogId = $this->getBlogRelatedToPhone($strUserKey);
				}
				if($intBlogId != -1)
				{
					$strConfigKey = 'wp_' . $intBlogId . '_user_config';

					$this->db->select('user_id,meta_value');
					$query = $this->db->get_where('wp_usermeta',array('meta_key'=>$strConfigKey));

					// echo "<br>GUD: <pre>";var_export($intBlogId);echo "</pre><br>";
					// exit();
					if($query->num_rows() > 0)
					{
						foreach($query->result() as $row)
						{
							$serialConfig = $row->meta_value;
							$arrConfig = unserialize($serialConfig);
							if($arrConfig['status'] == 'Active' && array_key_exists('study_phone_number', $arrConfig))
							{
								$strStudyPhone = str_replace('-', '', $arrConfig['study_phone_number']);
								if($strUserKey === $strStudyPhone)
								{
									$intUserId = $row->user_id;
									break;
								}
							}
						}
						$intUserId = (int)$intUserId;
						if ($intUserId == null) return null;
						// echo "<br>Found user<pre>";var_export($intUserId);echo "</pre><br>";
						// exit();
						$arrReturnResult = $this->standard_user_lookup(array($intUserId), $intBlogId);
					}
				}
				break;
			*/
        }

        return $arrReturnResult;
    }




    private function standard_user_lookup($arrUserId, $intBlogId = null, $includeUCP = false)
    {
        // initialize return result
        $arrReturnResult = [];

        // user id is always array, implode
        $intUserId = implode(',', $arrUserId);

        // get user(s)
        $wpUsers = User::whereRaw('id IN (' . $intUserId . ')')->get();

        if (!$wpUsers->isEmpty()) {
            foreach ($wpUsers as $wpUser) {
                $wpUserMeta = UserMeta::where('user_id', '=', $wpUser->id)->get();
                if (!$wpUsers->isEmpty()) {
                    $arrUserMeta = [];
                    foreach ($wpUserMeta as $meta) {
                        $arrUserMeta[$meta->meta_key] = $meta->meta_value;
                        // unserialize when needed
                        $mixSerializable = @unserialize($meta->meta_value);
                        if ($mixSerializable !== false || $meta->meta_value === 'b:0;') {
                            $arrUserMeta[$meta->meta_key] = $mixSerializable;
                        }
                    }
                }
                $arrUserData = (array)$wpUser->toArray();
                $arrReturnResult[$wpUser->id]['userdata'] = $arrUserData;
                $arrReturnResult[$wpUser->id]['usermeta_clean'] = $arrUserMeta;
                $arrReturnResult[$wpUser->id]['usermeta'] = $arrUserMeta;
                $arrReturnResult[$wpUser->id]['usermeta']['intProgramId'] = $intBlogId;
                $arrReturnResult[$wpUser->id]['usermeta']['msgtype'] = '';
                $arrReturnResult[$wpUser->id]['usermeta']['resend'] = false;
                $arrReturnResult[$wpUser->id]['usermeta']['comment_ID'] = '';
                $arrReturnResult[$wpUser->id]['usermeta']['curresp'] = '';
                $arrReturnResult[$wpUser->id]['usermeta']['state'] = [];
                $arrReturnResult[$wpUser->id]['usermeta']['user_care_plan'] = $this->get_user_care_plan(
                    $wpUser->id,
                    $intBlogId
                );
                if ($includeUCP) {
                    $arrReturnResult[$wpUser->id]['usermeta']['user_care_plan_items'] = $this->get_user_care_plan_items(
                        $wpUser->id,
                        $intBlogId
                    );
                }
            }
        }
        return $arrReturnResult;
    }

    public function get_user_care_plan(
        $intUserId,
        $intBlogId
    ) {
        $query = "SELECT
            rucp.ucp_id,
            rucp.items_id,
            rucp.user_id,
            rucp.meta_key,
            rucp.meta_value,
            im.meta_value as alert_key,
            parucp.meta_value as parent_status,
            parucp.ucp_id as parent_id
        FROM rules_ucp rucp
        INNER JOIN rules_itemmeta im on rucp.items_id = im.items_id
        INNER JOIN rules_items i on rucp.items_id = i.items_id
        INNER JOIN rules_pcp pcp on pcp.pcp_id = i.pcp_id
        INNER JOIN rules_ucp parucp on (i.items_parent = parucp.items_id AND rucp.user_id = parucp.user_id)
        WHERE rucp.user_id = " . $intUserId . "
        AND pcp.prov_id = " . $intBlogId . "
        AND im.meta_key = 'alert_key'";
        $rulesData = DB::connection('mysql_no_prefix')->select(DB::raw($query));
        // set alert_values
        $arrReturnResult = [];
        if (!empty($rulesData)) {
            foreach ($rulesData as $row) {
                $arrReturnResult[$row->alert_key] = [
                    'value'         => $row->meta_value,
                    'id'            => $row->ucp_id,
                    'parent_status' => $row->parent_status,
                ];
            }
            // hardcode severity limit of 7
            $arrReturnResult['Severity'] = ['value' => 7];
        }

        return $arrReturnResult;
    }

    public function get_user_care_plan_items(
        $userId,
        $int_id
    ) {

        // set blog id
        $this->int_id = $int_id;

        // query
        $query = DB::connection('mysql_no_prefix')->table('rules_ucp AS rucp');
        $query->select(
            'rucp.*',
            'pcp.pcp_id',
            'pcp.section_text',
            'i.items_parent',
            'i.items_id',
            'i.items_text',
            'rq.msg_id',
            'ims.meta_value AS ui_sort',
            'rip.qid AS items_parent_qid',
            'rqp.msg_id AS items_parent_msg_id'
        );
        $query->where('user_id', '=', $userId);
        $query->join('rules_items AS i', 'i.items_id', '=', 'rucp.items_id');
        $query->leftJoin('rules_items AS rip', 'i.items_parent', '=', 'rip.items_id'); // parent item info
        $query->join('rules_pcp AS pcp', function ($join) {
            $join->on('i.pcp_id', '=', 'pcp.pcp_id')->where('pcp.prov_id', '=', $this->int_id);
        });
        $query->leftJoin('rules_questions AS rq', 'rq.qid', '=', 'i.qid');
        $query->leftJoin('rules_questions AS rqp', 'rqp.qid', '=', 'rip.qid'); // parent question info
        $query->leftJoin('rules_itemmeta AS ims', function ($join) {
            $join->on('ims.items_id', '=', 'i.items_id')->where('ims.meta_key', '=', 'ui_sort');
        });
        $query->whereRaw("(rucp.meta_key = 'status' OR rucp.meta_key = 'value') AND user_id = " . $userId);
        $query->orderBy("ui_sort", 'ASC');
        $query->orderBy("i.items_id", 'DESC');
        $result = $query->get();

        $arrReturnResult = [];
        // set alert_values
        if (!empty($result)) {
            foreach ($result as $row) {
                $arrReturnResult[$row->items_id] = [
                    'msg_id'              => $row->msg_id,
                    'ui_sort'             => $row->ui_sort,
                    'meta_key'            => $row->meta_key,
                    'meta_value'          => $row->meta_value,
                    'pcp_id'              => $row->pcp_id,
                    'section_text'        => $row->section_text,
                    'items_id'            => $row->items_id,
                    'items_text'          => $row->items_text,
                    'items_parent'        => $row->items_parent,
                    'items_parent_qid'    => $row->items_parent_qid,
                    'items_parent_msg_id' => $row->items_parent_msg_id,
                ];
            }
        } else {
            echo "<br>MsgUser->get_user_care_plan_items() ERROR, could not find!";
        }

        return $arrReturnResult;
    }

    /**
     * get_readyusers_for_daily_reminder - method to construct an array with all users that are ready to receive a message cycle.
     * this method is an ovverride to the original get_readyusers, to clean it up and migrate everything over too
     *              [1] Get all users that are active participants.
     *              [2] Get all users that are active in the 'ca' program and check if today's DOW is in the preferred contact day list.
     *              [3] See if user is active in 'ca' user configuration meta (why a second time?) and get the preferred contact TOD.
     *              [4] Check if there already is a 'state' record created for this user, if not, needs to be added to return array.
     *
     * @param       integer depicting the program id.
     * @param       string
     * @param       bool
     * @param       bool
     * @return      array of user ids and related contact time information.
     *
     */
    public function get_readyusers_for_daily_reminder($intProgramID, $arrMsgType, $max, $logOutput = false)
    {
        date_default_timezone_set('America/New_York');
        $serverDateTime = new DateTime(null, new DateTimeZone('America/New_York'));
        //echo "<pre>";var_dump($serverDateTime->format('Y-m-d H:i:s T'));echo "</pre>";

        // we need both the full msg type and the abreviation ie. hospital / hsp
        if (isset($arrMsgType['msgTypeAbrev'])) {
            $msgType = $arrMsgType['msgType'];
            $msgTypeAbrev = $arrMsgType['msgTypeAbrev'];
        } else {
            return false;
        }

        // normally check for messages sent today.
        $tmpTodaySearch =  "and date(comment_date) = '". date('Y-m-d')."'";
        if (strtolower($msgType) == 'welcome') {
            //  If welcome message, check it message has ever been sent.
            $tmpTodaySearch = '';
        }

        $strCapabilitiesIdent = 'wp_' . $intProgramID . '_capabilities';
        $strCommentFile = 'wp_' . $intProgramID . '_comments';
        $strContactTimesContainer = 'wp_' . $intProgramID . '_user_config';

        $arrUserData = [];
        $arrAllParticipantUserIDs = [];
        $limit = '';
        if ($max > 0) {
            $limit = " LIMIT {$max}";
        }

        // original query segment before $tmpTodaySearch was created.
        // where comment_type like 'state_".$msgTypeAbrev."' and date(comment_date) = '". date('Y-m-d') ."' group by cmts.user_id) cm on cm.user_id = u.id

        $sql = "SELECT id, user_registered, user_status, c.meta_value `" . $strCapabilitiesIdent . "`, s.meta_value `" . $strContactTimesContainer . "`
                 FROM wp_users u
                left join wp_usermeta c on c.user_id = u.id
                left join wp_usermeta s on s.user_id = u.id
                LEFT JOIN (select cmts.user_id, max(comment_date) `last_comment` FROM ".$strCommentFile." cmts
                            where comment_type like 'state_" . $msgTypeAbrev . "' " . $tmpTodaySearch . " group by cmts.user_id) cm on cm.user_id = u.id
                WHERE
                    c.meta_key= '".$strCapabilitiesIdent."'
                and c.meta_value = 'a:1:{s:11:\"participant\";b:1;}'
                and s.meta_key = '".$strContactTimesContainer."'
                and s.meta_value like binary '%Active%'
                and cm.user_id is null ".$limit."";

        $userData = DB::connection('mysql_no_prefix')->select(DB::raw($sql));
        //dd($userData);

        $logString = '';
        if (!empty($userData)) {
            $d = 0;
            foreach ($userData as $row) {
                $arrCapabilities = unserialize($row->$strCapabilitiesIdent);
                $arrConfig = unserialize($row->$strContactTimesContainer);
// var_dump($arrConfig);

                // check if reminder time is set otherwise use contact time.
                if (empty($arrConfig[$msgType.'_reminder_time'])) {
                    $strPreferredContactTime = $arrConfig['preferred_contact_time'];
                } else {
                    $strPreferredContactTime = $arrConfig[$msgType.'_reminder_time'];
                }

                // $strPreferredContactTime = '11:00';
                $strContactTime = $this->conformTime($strPreferredContactTime);
                if (!$strPreferredContactTime == null) {
                    $strContactTime = new DateTime($serverDateTime->format('Y-m-d').$strPreferredContactTime.'', new DateTimeZone($arrConfig['preferred_contact_timezone']));
                } else {
                    continue;
                    $strContactTime = new DateTime($serverDateTime->format('Y-m-d'), new DateTimeZone($arrConfig['preferred_contact_timezone']));
                }
// error_log($row->id." ".$serverDateTime->format('Y-m-d')." $strPreferredContactTime");
// echo $row->id." $strPreferredContactTime";
                $userRegisteredDateTime = new DateTime($row->user_registered, new DateTimeZone('America/New_York'));
                // $strContactTime = $date->format('Y-m-d H:i:s T');

                if (!isset($arrConfig[$msgType.'_reminder_optin'])) {
                    $arrConfig[$msgType.'_reminder_optin'] = 'defaulting optin value';
                    $arrConfig[$msgType.'_reminder_time'] = 'defaulting time value';
                }
                if (!empty($userData)) {
                    if (strtolower($arrConfig['status']) === 'active' // Stops...duh!
                        && $serverDateTime->format('Y-m-d H:i:s T') >= date("Y-m-d", strtotime($arrConfig['active_date'])) // Stops messages going out before active date.
                        && $serverDateTime->format('U') > $strContactTime->format('U') // Stops messages going out before contact time
                        && $userRegisteredDateTime->format('U') < $strContactTime->format('U') // Stops messages going out on registration day
                        && $arrConfig[$msgType.'_reminder_optin'] != 'N'
                    ) {
// if ($serverDateTime->format('U') > $strContactTime->format('U') ) {echo "[".$row->id."]Contact before server time<BR>";} else
//  {echo "[".$row->id."]Contact after server time<BR>";}
// echo "Corrected Contact Time: [".$strContactTime->format('Y-m-d H:i:s T')."]<BR>".date("Y-m-d H:i:s T")."<BR><BR>";
                        $wpUser = User::find($row->id);
                        $userMeta = $wpUser->userMeta();
                        if (empty($userMeta['user_config'])) {
                            $logString .= "<br>MsgUser->get_readyusers_for_daily_reminder() [" . $row->id . "] Missing User Config";
                            continue 1;
                        }
                        $logString .= "<br>MsgUser->get_readyusers_for_daily_reminder() [" . $row->id . "]READY TO CHECK UCP";
                        $arrAllParticipantUserIDs[] = [
                            'user_id'                    => $row->id,
                            'status'                     => $arrConfig['status'],
                            $msgType . '_reminder_optin' => $arrConfig[$msgType . '_reminder_optin'],
                            $msgType . '_reminder_time'  => $arrConfig[$msgType . '_reminder_time'],
                        ];
                    } else {
                        $logString .= "<br>MsgUser->get_readyusers_for_daily_reminder() [" . $row->id . "]SKIP :: ";
                    }
                    // give a breakdown
                    if (strtolower($arrConfig['status']) !== 'active') {
                        $logString .= "[Bad Status ". ucfirst(strtolower($arrConfig['status'])) . "] ";
                    }
                    if (date("Y-m-d", strtotime($arrConfig['active_date'])) > date("Y-m-d")) {
                        $logString .= "[Not at active date ". date("Y-m-d", strtotime($arrConfig['active_date'])) . " > ". date("Y-m-d") . "] ";
                    }
                    if ($serverDateTime->format('U') < $strContactTime->format('U')) {
                        $logString .= "[Not at Contact time ". $serverDateTime->format('Y-m-d H:i:s T') . " Earlier Than ". $strContactTime->format('Y-m-d H:i:s T') . "] ";
                    }
                    if ($userRegisteredDateTime->format('U') >= $strContactTime->format('U')) {
                        $logString .= "[Don't send on Reg date if Reg time: {". $userRegisteredDateTime->format('Y-m-d H:i:s T') . "} is after Contact Time {". $strContactTime->format('Y-m-d H:i:s T') . "}] ";
                    }
                    if (isset($arrConfig[$msgType.'_reminder_optin']) && $arrConfig[$msgType.'_reminder_optin'] == 'N') {
                        $logString .= "[".$msgType."_reminder_optin == N] ";
                    }
                    //$logString .= "<br>";
                }
            }
        }
        //echo "<pre>";var_dump($logString);echo "</pre>";
        if ($logOutput) {
            echo '<br>MsgUser->get_readyusers_for_daily_reminder logstring' . $logString;
        }

        //echo "WOW";
        //dd($arrAllParticipantUserIDs);
        return $arrAllParticipantUserIDs;
    }

    /**
     * Helper method to nomilize the timezone inside the preferred time.
     * It's a long story why, but I believe I had something to do with it.
     */
    private function conformTime($strTime)
    {
        $strSearchTZ = ['ET','MT','CT','PT'];

        if (date('I', time())) {
            $strReplaceTZ = ['EDT','MDT','CDT','PDT'];
        } else {
            $strReplaceTZ = ['EST','MST','CST','PST'];
        }

        $strReplace = [''];
        $strTimeTZ = str_replace($strSearchTZ, $strReplaceTZ, $strTime);
        $strConformTime = date("Y-m-d H:i:s", strtotime($strTimeTZ));

        return $strConformTime;
    }

    public function get_readyusers($intProgramID = 0, $max)
    {
        date_default_timezone_set('America/New_York');
        $serverDateTime = new DateTime(null, new DateTimeZone('America/New_York'));

        $strCapabilitiesIdent = 'wp_' . $intProgramID . '_capabilities';
        $strCommentFile = 'wp_' . $intProgramID . '_comments';
        $strContactTimesContainer = 'wp_' . $intProgramID . '_user_config';

        $arrUserData = [];
        $arrAllParticipantUserIDs = [];
        $limit = "";
        if ($max > 0) {
            $limit = " LIMIT $max";
        }
        $sql = "SELECT id, user_registered, user_status, c.meta_value `$strCapabilitiesIdent`, s.meta_value `$strContactTimesContainer`
                 FROM wp_users u
                left join wp_usermeta c on c.user_id = u.id
                left join wp_usermeta s on s.user_id = u.id
                LEFT JOIN (select cmts.user_id, max(comment_date) `last_comment` FROM $strCommentFile cmts
                            where comment_type like 'state_sol' and date(comment_date) = '" . $serverDateTime->format('Y-m-d') . "' group by cmts.user_id) cm on cm.user_id = u.id
                WHERE
                    c.meta_key='".$strCapabilitiesIdent."'
                and c.meta_value = 'a:1:{s:11:\"participant\";b:1;}'
                and s.meta_key = '".$strContactTimesContainer."'
                and s.meta_value like '%Active%'
                and cm.user_id is null $limit;";

        $userData = DB::connection('mysql_no_prefix')->select(DB::raw($sql));
        if (!empty($userData)) {
            foreach ($userData as $row) {
                $arrCapabilities = unserialize($row->$strCapabilitiesIdent);
                $arrConfig = unserialize($row->$strContactTimesContainer);
// var_dump($arrConfig);
                $strPreferredContactTime = $arrConfig['preferred_contact_time'];
                // $strContactTime = $this->conformTime($strPreferredContactTime);
                $strContactTime = new DateTime($serverDateTime->format('Y-m-d ').$strPreferredContactTime, new DateTimeZone($arrConfig['preferred_contact_timezone']));
                $userRegisteredDateTime = new DateTime($row->user_registered, new DateTimeZone('America/New_York'));

                if (!empty($userData)) {
                    if (strtolower($arrConfig['status']) === 'active' // Stops...duh!
                        && $serverDateTime->format('Y-m-d H:i:s T') >= date("Y-m-d", strtotime($arrConfig['active_date'])) // Stops messages going out before active date.
                        &&  $serverDateTime->format('U') > $strContactTime->format('U') // Stops messages going out before contact time
                        && $userRegisteredDateTime->format('U') < $strContactTime->format('U') // Stops messages going out on registration day
                    ) {
                        $arrAllParticipantUserIDs[] = [
                            'user_id'                => $row->id,
                            'preferred_contact_time' => $arrConfig['preferred_contact_time'],
                        ];
                    } else {
                        echo "<br>MsgUser->get_readyusers() IS: " . $row->id . " " . $userRegisteredDateTime->format('Y-m-d H:i:s T') . " < " . $strContactTime->format('Y-m-d H:i:s T') . " but not until: " . date(
                            "Y-m-d",
                            strtotime($arrConfig['active_date'])
                        ) . ". Status: " .
                            strtolower($arrConfig['status']) . ". " . "";
                    }
                }
            }
        }

// var_export($arrAllParticipantUserIDs);
// exit('Done');
        return $arrAllParticipantUserIDs;
    }

    public function get_comments_for_user($userId, $blogId)
    {
        $commentTable = 'wp_'.$blogId.'_comments';
        $query = DB::connection('mysql_no_prefix')->table($commentTable . ' AS cm');
        $query->select("cm.*");
        $where = ['cm.user_id' => $userId];
        $query->where($where);
        $query->orderBy("cm.comment_ID", 'DESC');
        $query->limit('20');
        $comments = $query->get();
        return $comments;
    }


    public function get_users_for_active_item(
        $items_id,
        $int_id
    ) {
        $this->db->select("ucp.user_id, ucp.items_id, ucp.meta_value", false);
        $this->db->from('wp_users AS u');
        $this->db->join('rules_ucp AS ucp', 'u.id = ucp.user_id');
        $this->db->join('rules_items ri', 'ri.items_id = ucp.items_id');
        $this->db->join('rules_pcp pcp', 'ri.pcp_id = pcp.pcp_id');
        $where = ['ucp.items_id'   => $items_id,
                       'ucp.meta_value' => 'Active',
                       'pcp.prov_id'    => $int_id,
        ];
        $this->db->where($where);
        $this->db->order_by("ucp.user_id", 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     *   @todo Get this working for response to the open state record [PML] - 2/10
     *
     **/

    public function userSmsState($arrUserData)
    {
        //New Unsolicited Msg Flow request
        $strReturnResult = '';
        $intUserId = key($arrUserData);
        $arrUserData[$intUserId]['usermeta']['msgtype'] = $arrUserData[$intUserId]['usermeta']['curresp'];
        $strCommentsTable = 'wp_' . $arrUserData[$intUserId]['usermeta']['intProgramId'] . '_comments';
        if (in_array(strtoupper($arrUserData[$intUserId]['usermeta']['curresp']), ["RPT","SYM","R","S","CALL","H","HSP"]) > 0) {
            switch (strtoupper($arrUserData[$intUserId]['usermeta']['curresp'])) {
                case 'R':
                    $arrUserData[$intUserId]['usermeta']['curresp'] = 'RPT';
                    break;

                case 'S':
                    $arrUserData[$intUserId]['usermeta']['curresp'] = 'SYM';
                    break;
                case 'H':
                    $arrUserData[$intUserId]['usermeta']['curresp'] = 'HSP';
                    break;
                default:
                    break;
            }
            $arrMsgType = $arrUserData[$intUserId]['usermeta']['curresp'];
            $sql = "SELECT comment_ID, comment_type,comment_content FROM $strCommentsTable WHERE (comment_type LIKE 'state_$arrMsgType') and user_id=$intUserId and DATE(comment_date)=DATE(NOW()) AND comment_approved = 0 ORDER BY comment_date DESC LIMIT 1";
            $query = DB::connection('mysql_no_prefix')->select(DB::raw($sql));

            if (!empty($query)) {
                $row = $query[0];
                $state = unserialize($row->comment_content);
                $arrUserData[$intUserId]['usermeta']['msgtype'] = strtoupper($arrMsgType);
                $arrUserData[$intUserId]['usermeta']['comment_ID'] = (int)$row->comment_ID;
                $arrUserData[$intUserId]['usermeta']['state'] = $state;
            } else {
                $arrUserData[$intUserId]['usermeta']['msgtype'] = strtoupper($arrUserData[$intUserId]['usermeta']['curresp']);
                $rec = $this->create_new_unsolicited_comment_row($arrUserData);
                $arrUserData[$intUserId]['usermeta']['comment_ID'] = $rec;
            }

            $arrUserData[$intUserId]['usermeta']['curresp'] = null;
        } else {
            // Existing Msg Flow session Response
            $sql = "SELECT comment_ID, comment_type,comment_content FROM $strCommentsTable WHERE (comment_type LIKE 'state_%') and user_id=$intUserId and DATE(comment_date)=DATE(NOW()) AND comment_approved = 0 ORDER BY comment_date DESC LIMIT 1";
            $query = DB::connection('mysql_no_prefix')->select(DB::raw($sql));

            if (!empty($query)) {
                $row = $query[0];
                $state = unserialize($row->comment_content);
                $arrMsgType = explode("_", $row->comment_type);
                $arrUserData[$intUserId]['usermeta']['msgtype'] = strtoupper($arrMsgType[1]);
                $arrUserData[$intUserId]['usermeta']['comment_ID'] = (int)$row->comment_ID;
                $arrUserData[$intUserId]['usermeta']['state'] = $state;
            }
        }

// echo "userSmsState:<BR>$sql<BR>$strReturnResult<pre>"; var_export($arrUserData);
// exit();
        return $arrUserData;
    }

    public function create_new_unsolicited_comment_row($arrUserData)
    {
        date_default_timezone_set('America/New_York');

        $strCommentsTable = 'wp_' . $arrUserData[key($arrUserData)]['usermeta']['intProgramId'] . '_comments';
        $arrUnsolicitedData = [
            'comment_author'       => 'MsgUser',
            'comment_author_email' => 'admin@medadherence.com',
            'comment_author_url'   => 'https://medadherence.com/',
            'comment_content'      => serialize([]),
            'comment_type'         => 'state_' . strtolower($arrUserData[key($arrUserData)]['usermeta']['msgtype']),
            'comment_parent'       => 0,
            'user_id'              => key($arrUserData),
            'comment_author_IP'    => '127.0.0.1',
            'comment_agent'        => '',
            'comment_date'         => date('Y-m-d H:i:s'),
            'comment_approved'     => 0,
        ];

        $comment_id = DB::connection('mysql_no_prefix')->table($strCommentsTable)->insertGetId($arrUnsolicitedData);
        echo "<br>MsgDelivery->create_new_unsolicited_comment_row() Created New Comment = " . $comment_id;

        return $comment_id;
    }

    public function get_all_active_participants($intBlogId = null)
    {
// This is a BAD way if doing this.....only used by the original cpm_1_5_datamonitor.php not for v1.7
        $arrReturnResult = [];

        $strCapabilityKey = 'wp' . $intBlogId . '_capabilities';
        $strProgramKey = 'wp' . $intBlogId . '_ca';
        $strConfigKey = 'wp' . $intBlogId . '_user_config';

// Temp
        $strProgramKey = 'first_name';
        $strConfigKey = $strCapabilityKey;

        $sql = "SELECT
                    u1.user_id AS user_id,u1.meta_key, u1.meta_value AS capability ,u2.meta_value AS program, u3.meta_value AS config
                FROM
                    wp_usermeta AS u1
                INNER JOIN
                    wp_usermeta AS u2
                ON
                    u1.user_id=u2.user_id
                INNER JOIN
                    wp_usermeta AS u3
                ON
                    u1.user_id=u3.user_id
               WHERE
                   u1.meta_key=?
               AND
                   u2.meta_key=?
                AND
                    u3.meta_key=?
                ORDER BY
                    u1.user_id";

        $query = $this->db->query($sql, [$strCapabilityKey, $strProgramKey, $strConfigKey]);

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
// DELETE THIS LINE
                $arrReturnResult[] = $row->user_id;
// DELETE THAT LINE


                $arrCapabilities = @unserialize($row->capability);
                if (isset($arrCapabilities['participant'])) {
                    if ($arrCapabilities['participant'] == true) {
                        $arrProgram = @unserialize($row->program);

// BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD

                        if (isset($arrProgram['ca'])) {
                            if (strtolower($arrProgram['ca']) == 'active') {
// BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD BAD

                                $arrConfig = @unserialize($row->config);

                                if (isset($arrConfig['status'])) {
                                    // added active date check 11/18 [PML]
                                    if (strtolower($arrConfig['status']) == 'active' && date("Y-m-d") >= date("Y-m-d", strtotime($arrConfig['active_date']))) {
                                        $arrReturnResult[] = $row->user_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $arrReturnResult;
    }

    public function checkIfUserIsPassedActivationDate($intUserId, $intBlogId, $strCheckDate = null)
    {
        date_default_timezone_set('America/New_York');

        if ($strCheckDate === null) {
            $dateToday = new DateTime(date('Y-m-d'));
        } else {
            $dateToday = new DateTime(date('Y-m-d', strtotime($strCheckDate)));
        }

        $boolIsActive = false;
        $strMetaKey = 'wp_' . $intBlogId . '_user_config';
        $sql = "SELECT meta_value FROM wp_usermeta WHERE user_id=? AND meta_key=? LIMIT 1";

        $query = $this->db->query($sql, [$intUserId, $strMetaKey]);

        if ($query->num_rows() > 0) {
            $row = $query->row();

            $serialUserConfig = $row->meta_value;
            $arrUserConfig = unserialize($serialUserConfig);

            if (isset($arrUserConfig['active_date'])) {
                $dateActiveDate = new DateTime($arrUserConfig['active_date']);
                echo '<br>MsgUser->checkIfUserIsPassedActivationDate() ' . $dateToday->format('Y-m-d').'<br />'.$dateActiveDate->format('Y-m-d').'<br />'.$dateActiveDate->diff($dateToday)->format('%R%a').'<br />';
                if ($dateActiveDate->diff($dateToday)->format('%R%a') >= 0) {
                    $boolIsActive = true;
                }
            }
        }

        return $boolIsActive;
    }

    public function getMD5UserIds()
    {
        $arrReturnResult = [];
        $arrId2MD5 = [];
        $arrMD52Id = [];

        $sql = "SELECT id FROM wp_users";
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $arrId2MD5[$row->id] = md5($row->id);
                $arrMD52Id[md5($row->id)] = $row->id;
            }
        }
        $arrReturnResult['Id2MD5'] = $arrId2MD5;
        $arrReturnResult['MD52Id'] = $arrMD52Id;

        return $arrReturnResult;
    }

    public function get_provider_care_plan($intUserId, $intBlogId)
    {
        $query = $this->db->query("select * from rules_question_sets qs
 left join rules_questions q on q.qid = qs.QID
 left join rules_answers a on a.aid = qs.aid
 where qs_type = 'sol'
-- where qs_type = 'CALL'
-- where qs_type = 'RPT'
-- where qs_type = 'REM'
and qs.Provider_ID = {$intBlogId}
order by qs.qs_type, qs.sort, qs.aid
;;");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // var_export($row);
                foreach ($row as $key => $value) {
                    $arrValue = unserialize($value);
                    if ($arrValue !== false) {
                        $value = $arrValue;
                    }
                    $arrReturnResult[$row->meta_key] = $value;
                }
            }
        }
        return $arrReturnResult;
    }

    public function get_provider_care_plan_preferences($intUserId, $intBlogId)
    {
        $query = $this->db->query("SELECT * FROM wp_". $intBlogId . "_postmeta where post_id = (SELECT id FROM wp_". $intBlogId . "_posts where post_type = 'care_plan');");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // var_export($row);
                foreach ($row as $key => $value) {
                    $arrValue = unserialize($value);
                    if ($arrValue !== false) {
                        $value = $arrValue;
                    }
                    $arrReturnResult[$row->meta_key] = $value;
                }
            }
        }
        return $arrReturnResult;
    }

    public function get_blog_domain($intBlogId)
    {
        $this->db->select('b.domain');
        $this->db->from('wp_blogs AS b');
        $this->db->where(['b.id' => $intBlogId]);
        $query = $this->db->get();
        $result = $query->row();
        if (!empty($result)) {
            return $result->domain;
        }
        return false;
    }

    /**
     * @param $user_info
     * @param $user_meta
     * @param $int_id
     * @return bool
     */
    public function create_new_user(
        $user_info,
        $user_meta,
        $int_id
    ) {
        // first make sure user doesnt already exist
        $this->db->select('u.*');
        $this->db->from('wp_users AS u');
        $this->db->where("u.username = '" . $user_info['username'] . "'");
        $query = $this->db->get();
        $user_exists = $query->row();
        // return false is user exists and no overwrite
        if ($user_exists) {
            return false;
        }

        // id, username, password, user_nicename, email, user_url, user_registered, user_activation_key, user_status, display_name, spam, deleted
        $this->db->insert('wp_users', $user_info);
        $new_user_id = $this->db->insert_id();

        // nickname, first_name, last_name, description, rich_editing, comment_shortcuts, admin_color, user_ssl, show_admin_bar_front
        // wp_8_capabilities, wp_8_user_level, wp_8_user_config, dismissed_wp_pointers
        if (!empty($user_meta)) {
            $this->db->delete("wp_usermeta", ['user_id' => $new_user_id]);
            foreach ($user_meta as $key => $value) {
                $meta_value = $value;
                if (is_array($meta_value)) {
                    $meta_value = serialize($meta_value);
                }
                $this->db->insert('wp_usermeta', ['user_id' => $new_user_id, 'meta_key' => $key, 'meta_value' => $meta_value]);
            }
        }
        return $new_user_id;
    }

    /**
     * @param $user_info
     * @param $user_meta
     * @param $ucp_items
     * @param $overwrite
     * @param $int_id
     * @return bool
     */
    public function import_user(
        $user_info,
        $user_meta,
        $ucp_items,
        $overwrite,
        $int_id
    ) {
        // first make sure user doesnt already exist
        $this->db->select('u.*');
        $this->db->from('wp_users AS u');
        $this->db->where("u.username = '" . $user_info['username'] . "'");
        $query = $this->db->get();
        $user_exists = $query->row();
        //echo "<pre>";var_dump($this->db->last_query());echo "</pre>";
        //echo "<pre>";var_dump($user_exists);echo "</pre>";

        // return false is user exists and no overwrite
        if (!$overwrite && !empty($user_exists)) {
            return false;
        }
        if (empty($user_exists)) {
            // id, username, password, user_nicename, email, user_url, user_registered, user_activation_key, user_status, display_name, spam, deleted
            unset($user_info['id']);
            $this->db->insert('wp_users', $user_info);
            $new_user_id = $this->db->insert_id();
        } else {
            $new_user_id = $user_exists->id;
        }
        // nickname, first_name, last_name, description, rich_editing, comment_shortcuts, admin_color, user_ssl, show_admin_bar_front
        // wp_8_capabilities, wp_8_user_level, wp_8_user_config, dismissed_wp_pointers
        if (!empty($user_meta)) {
            $this->db->delete("wp_usermeta", ['user_id' => $new_user_id]);
            $user_meta['first_name'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
            $user_meta['last_name'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            foreach ($user_meta as $key => $value) {
                $meta_value = $value;
                if (is_array($meta_value)) {
                    $meta_value['study_phone_number'] = '203-252-2556';
                    $meta_value = serialize($meta_value);
                }
                $this->db->insert('wp_usermeta', ['user_id' => $new_user_id, 'meta_key' => $key, 'meta_value' => $meta_value]);
            }
        }

        // ucp
        $this->db->delete("rules_ucp", ['user_id' => $new_user_id]);
        if (!empty($ucp_items)) {
            foreach ($ucp_items as $ucp_item) {
                $this->db->insert('rules_ucp', ['items_id' => $ucp_item['items_id'], 'user_id' => $new_user_id, 'meta_key' => $ucp_item['meta_key'], 'meta_value' => $ucp_item['meta_value']]);
            }
        }

        return $new_user_id;
    }

    /**
     * @param $user_id
     * @param $comment_data
     * @param $int_id
     */
    public function import_user_observation_data(
        $user_id,
        $comment_data,
        $int_id
    ) {
        // build tables to use
        //$str_observation_table = 'ma_' . $int_id . '_observations';
        $str_observation_table = 'lv_observations';
        //$str_observationmeta_table = 'ma_' . $int_id . '_observationmeta';
        $str_observationmeta_table = 'lv_observationmeta';
        //$str_comments_table = 'wp_' . $int_id . '_comments';
        $str_comments_table = 'lv_comments';

        // remove all users comment data
        $this->db->delete("{$str_comments_table}", ['user_id' => $user_id]);
        $this->db->query("
DELETE  {$str_observationmeta_table}.* FROM {$str_observationmeta_table}
INNER JOIN {$str_observation_table} ON ({$str_observation_table}.obs_id = {$str_observationmeta_table}.obs_id)
WHERE {$str_observation_table}.user_id = ?;", [$user_id]);
        $this->db->delete("{$str_observation_table}", ['user_id' => $user_id]);

        // insert comments on target server
        foreach ($comment_data as $comment) {
            $comment['user_id'] = $user_id;
            // insert comment
            unset($comment['comment']['comment_ID']);
            $this->db->insert("{$str_comments_table}", $comment['comment']);
            $comment_id = $this->db->insert_id();
            // with comment id, insert observations
            if (!empty($comment['observations'])) {
                foreach ($comment['observations'] as $observation) {
                    $observation_params = $observation;
                    $observation_params['comment_id'] = $comment_id;
                    $observation_params['user_id'] = $user_id;
                    unset($observation_params['obs_id']);
                    if (isset($observation_params['observationmeta'])) {
                        unset($observation_params['observationmeta']);
                    }
                    // insert observation
                    $this->db->insert("{$str_observation_table}", $observation_params);
                    $obs_id = $this->db->insert_id();
                    if (!empty($observation['observationmeta'])) {
                        foreach ($observation['observationmeta'] as $observationmeta) {
                            $observationmeta_params = $observationmeta;
                            unset($observationmeta_params['meta_id']);
                            $observationmeta_params['obs_id'] = $obs_id;
                            $observationmeta_params['comment_id'] = $comment_id;
                            // insert observationmeta
                            $this->db->insert("{$str_observationmeta_table}", $observationmeta_params);
                            $obsmeta_id = $this->db->insert_id();
                        }
                    }
                }
            }
        }
    }

    public function get_user_state_record_by_id($intProgramId, $id)
    {
        echo "<br>MsgUser->get_user_state_record_by_id() id=".$id;
        $query = DB::connection('mysql_no_prefix')->table('wp_'. $intProgramId .'_comments');
        $query->select('*');
        $query->where('comment_ID', '=', $id);
        $record_exists = $query->get();

        // echo "<BR>Users State: [$id]<BR>";
        // var_export($record_exists);
        if ($record_exists) {
            // var_dump($record_exists);
            return $record_exists;
        } else {
            return false;
        }
    }

    private function getBlogRelatedToPhone($strUserKey)
    {
        $intBlogId = -1;
        $query = $this->db->query("SELECT user_id,meta_key,meta_value FROM wp_usermeta WHERE meta_key LIKE 'wp_%_user_config'");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $serialConfig = $row->meta_value;
                $arrConfig = unserialize($serialConfig);
                if ($arrConfig !== false) {
                    if (array_key_exists('study_phone_number', $arrConfig)) {
                        $strStudyPhone = preg_replace('/[^0-9]/', '', $arrConfig['study_phone_number']);
                        if ($strUserKey == $strStudyPhone) {
                            $intUserId = $row->user_id;
                            $intBlogId = $this->extract_blogid($row->meta_key);
                        }
                    }
                }
            }
        }

        return $intBlogId;
    }

    private function extract_blogid($strMetaKey)
    {
        $intBlogId = $strMetaKey[3];

        if ($strMetaKey[4] !== '_') {
            $intBlogId .= $strMetaKey[4];
        }

        return (int)$intBlogId;
    }
}
