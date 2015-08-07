<?php
if(!defined('BASEPATH')) exit ('No direct access allowed.');

class MsgCPRules {

    /**
     * rules_model
     *
     * @author 		mrand@mcirclelinkhealth.com
     * @copyright 	CircleLink Health, LLC - 01/30/2015
     *
     */

    private $_ci;


    public function __construct() {

    }

    public function getValidAnswer($pid, $qstype, $strMsgID='', $strResponse='') {
        /**
         *
         *	@internal 	Returns instructions for handling messages.
         *	@param 		pid: ProviderID
         *				qstype:  Question group type (SOL, UNS, RPT, etc..)
         *				strMsgID: Title of question to look up response for.
         *				strResponse: actual value sent by user
         *	@return 	question set record with infromation on how to proceed based on user response
         *	@todo		Currently query may blurr question categories between list, Range; FreeText may not be combined at this time
         *
         */

        // $tmpArray = explode(' ', $strResponse);
        $tmpArray = preg_split("/[ _]/", $strResponse);
        $strResponse2 = $tmpArray[0];

        // echo '<br>Valid tmpArray: ';
        // print_r($tmpArray);

        $qdata = $this->getQuestion($strMsgID, 0, '', $pid);
        // check for Blood Pressure format
        if($qdata->obs_key == 'Blood_Pressure'){
            // make sure both halves contain numbers else reject
            if(!is_numeric($tmpArray[0]) || empty($tmpArray[1]) || !is_numeric($tmpArray[1])){
                return array();
            }
        }

        // check for mm/dd format
        if($qdata->obs_key == 'HSP_ER' || $qdata->obs_key == 'HSP_HOSP'){
            $mixedReturn = $this->getMixedValid($strMsgID, $pid, $strResponse);
            if(empty($mixedReturn)) {
                $testStr = str_replace( '_', '/', $strResponse);
                if(!isset($tmpArray[0]) || $tmpArray[0] <> date('m', strtotime($testStr))) {
                    return array();
                } elseif (!isset($tmpArray[1]) || $tmpArray[1] <> date('d', strtotime($testStr))) {
                    return array();
                }
            }
        }


        // // check for no response, this should prevent an valid answer being found.
        // if(empty($strResponse)) {
        // 	$strResponse2 = 'Blank_Response';
        // }


        $strQS	= '';
        if(!empty($qstype)) {
            $strQS	= " AND qs.qs_type = '{$qstype}' ";
        }

        $query = <<<query
select qs.*, if(ISNULL(a.value), '{$strResponse}', a.value) as value
FROM rules_question_sets qs
LEFT JOIN rules_questions q using (qid)
LEFT JOIN rules_answers a using (aid)
WHERE qs.provider_id = {$pid}
{$strQS}
AND if('' = '{$strMsgID}', true, q.msg_id = '{$strMsgID}')
and (CONCAT(',',a.value,',',a.alt_answers,',') rlike ',{$strResponse2},'
or IF('{$strResponse2}' RLIKE '^-?[0-9]+$', '{$strResponse2}', -999) between qs.low and qs.high
or q.qtype rlike 'FreeText|None|End|TOD')
LIMIT 1
query;

// echo '<br>'.$query;

        $results = $this->db->query($query);

        return $results->row();

    }//getValidAnswer

    public function getMixedValid($strMsgId, $pid, $strResponse) {

        $query = <<<query
select qs.*
from rules_question_sets qs
join rules_questions q on q.qid = qs.qid
join rules_answers a on a.aid = qs.aid
where provider_id = {$pid}
and q.msg_id = '{$strMsgId}'
and CONCAT(',',a.value,',',a.alt_answers,',') rlike ',{$strResponse},'
LIMIT 1
query;

        echo '<br>'.$query;

        $results = $this->db->query($query);

        return $results->row();

    } //getMixedValid

    public function getQuestion($strMsgId, $intUserId=0, $strMsgText='SMS_EN', $pid = '0', $qstype = 'SOL') {
        /**
         *
         *	@internal 	Returns question information for next question to ask.
         *	@param 		strMsgId: Message ID
         *	@return 	question data
         *	@todo		Currently query may blurr question categories between list, Range; FreeText may not be combined at this time
         *
         */
// if((u2.meta_value = 'Active' or im2.meta_value = 'Active'), 'Active', 'Inactive') as status

        // remove leading and trailing spaces for msgId
        $strMsgId = trim($strMsgId);

        $query = <<<query
select q.qid, q.msg_id, q.qtype, im.meta_key as msgtype, im.meta_value as message, q.obs_key,
if(isnull(p.meta_value), ',,', p.meta_value) as cdays,
ifnull(u2.meta_value, 'Inactive') as ucp_status,
ifnull(im2.meta_value, 'Inactive') as pcp_status, qs.action
FROM rules_questions q
left join rules_question_sets qs on qs.qid = q.qid and qs_type = '{$qstype}' and qs.provider_id = {$pid}
join rules_items i on i.qid = q.qid
join rules_pcp pc on pc.pcp_id = i.pcp_id and pc.prov_id = {$pid}
left join rules_itemmeta im on im.items_id = i.items_id and im.meta_key = '{$strMsgText}'
left join rules_itemmeta im2 on im2.items_id = i.items_id and im2.meta_key = 'AllPatients'
left join rules_items i2 on i2.items_parent = i.items_id and i2.items_text = 'Contact Days'
left join rules_ucp p on p.items_id = i2.items_id and p.user_id = {$intUserId}
left join rules_ucp u2 on u2.items_id = i.items_id and u2.meta_key = 'status' and u2.user_id = {$intUserId}
WHERE msg_id = '{$strMsgId}'
limit 1
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->row();

    }//getQuestion


    public function getQuestionById($intID, $intUserId=0, $strMsgText='SMS_EN', $pid = '0', $qstype = 'SOL') {
        /**
         *
         *	@internal 	Returns question information for next question to ask.
         *	@param 		strMsgId: Message ID
         *	@return 	question data
         *	@todo		Currently query may blurr question categories between list, Range; FreeText may not be combined at this time
         *
         */

// if((u2.meta_value = 'Active' or im2.meta_value = 'Active'), 'Active', 'Inactive') as status

        $query = <<<query
select q.qid, q.msg_id, q.qtype, im.meta_key as msgtype, im.meta_value as message, q.obs_key,
if(isnull(p.meta_value), ',,', p.meta_value) as cdays,
ifnull(u2.meta_value, 'Inactive') as pcp_status,
ifnull(im2.meta_value, 'Inactive') as ucp_status, qs.action
FROM rules_questions q
left join rules_question_sets qs on qs.qid = q.qid and qs_type = '{$qstype}' and qs.provider_id = {$pid}
left join rules_items i on i.qid = q.qid
join rules_pcp pc on pc.pcp_id = i.pcp_id and pc.prov_id = {$pid}
left join rules_items i2 on i2.items_parent = i.items_id and i2.items_text = 'Contact Days'
left join rules_itemmeta im on im.items_id = i.items_id and im.meta_key = '{$strMsgText}'
left join rules_itemmeta im2 on im2.items_id = i.items_id and im2.meta_key = 'AllPatients'
left join rules_ucp p on p.items_id = i2.items_id and p.user_id = {$intUserId}
left join rules_ucp u2 on u2.items_id = i.items_id and u2.meta_key = 'status' and u2.user_id = {$intUserId}
WHERE q.qid = {$intID}
LIMIT 1
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->row();

    }//getQuestionById


    public function getNextList($provid, $user_id, $qstype, $qtype) {
        /**
         *
         *	@internal 	Returns question set for next question to ask.
         *	@param 		provid: provider id
         *				qstype: question set name or group of questions (ex: SOL, RPT, SYM, etc....)
         *	@return 	array of questions
         *
         */

        $query = <<<query
SELECT i.items_id, q.msg_id, q.qtype, im.meta_value as text, 
 ifnull(u.meta_value, ',,') as cdays,
 ifnull(u2.meta_value, 'Inactive') as ucp_status, 
 ifnull(im2.meta_value, 'Inactive') as pcp_status, qs.action, q.obs_key
FROM rules_question_sets qs
join rules_items i on i.qid=qs.qid
join rules_pcp p on p.pcp_id = i.pcp_id and p.prov_id = {$provid}
join rules_questions q on q.qid = qs.qid
join rules_itemmeta im on im.items_id = i.items_id and im.meta_key = '{$qtype}'
left join rules_itemmeta im2 on im2.items_id = i.items_id and im2.meta_key = 'AllPatients'
left join rules_items cd on cd.items_parent = i.items_id and cd.items_text = 'Contact Days'
left join rules_ucp u on u.items_id = cd.items_id and u.meta_key = 'value' and u.user_id = {$user_id}
left join rules_ucp u2 on u2.items_id = i.items_id and u2.meta_key = 'status' and u2.user_id = {$user_id}
where  qs.qs_type = '{$qstype}'
and qs.provider_id = {$provid}
-- and (u2.meta_value = 'Active' or im2.meta_value = 'Active')
group by i.items_id
order by qs.qs_sort
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->result_array();

    }//getNextList


    public function getAdherenceCounts($provid, $user_id) {
        /**
         *
         *	@internal 	Counts responses to adherence questions plus how many should have been asked
         *	@return 	array of counts (should be 3 rows)
         *
         */

        $query = <<<query
select obs_unit, obs_value, count(*) as count
from ma_{$provid}_observations
where user_id = {$user_id}
and obs_key = 'Adherence'
and date(obs_date) = date(now())
and obs_unit in ('','scheduled')
group by obs_unit, obs_value
order by obs_unit, obs_value;
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->result_array();

    }//getAdherenceCounts



    // Saves response to comments table
    public function saveResponse($input, $question, $intProvID='0', $obs_key='Other', $obs_unit='')	{

        /**
         *
         *	@internal 	Saves response to comment_table and sends response to obs_processor
         *	@param 		input: user arrray
         *		 		question: MsgID of question
         *  			intProvID: which system is calling this (7 = Crisfield)
         *	@return 	nothing
         *
         */

        $CID = $input['usermeta']['comment_ID'];

        if(empty($CID)) {
            error_log('saveResonse: comment_ID missing.');
            return;
        }

        $query = <<<query
		select * 
		from wp_{$intProvID}_comments 
		where comment_ID={$CID}
		limit 1
query;


        $results = $this->db->query($query);
        $row   = $results->row();

        // echo '<br>From save2/$row: '; print_r($row);

        //replace the response
        $dbState	= unserialize($row->comment_content);
        // only continue if we have comment_content
        if(is_array($dbState) && !empty($dbState)) {
            error_log('Recieved: '.urldecode($input[usermeta]['curresp']));
            end($dbState);
            $lastkey	= key($dbState);
            foreach ($dbState[$lastkey] as $key => $value) {
                $dbState[$lastkey][$key] = urldecode($input[usermeta]['curresp']);
            }
            // $dbState[$question]  .= $input[usermeta]['curresp'];
            $serializedState 	 = serialize($dbState);

            //save it back
            $saveq = <<<query2
			Update wp_{$intProvID}_comments
			set comment_content = '{$serializedState}'
			where comment_ID = {$CID}
query2;

            $results2 = $this->db->query($saveq);

            // special handling need to put the forward slashes back in for BP
            if($obs_key == 'Blood_Pressure'){
                $obs_value = preg_replace("/[ _]/", "/", trim($input[usermeta]['curresp']));
            } else {
                $obs_value = trim($input[usermeta]['curresp']);
            }

            //flag obs_processor of response
            $data = array(
                'comment_id' => $row->comment_ID,
                'sequence_id' => $lastkey,
                'obs_message_id' => $question,
                'obs_key' => $obs_key,
                'obs_value' => $obs_value,
                'obs_unit' => $obs_unit
            );
            error_log('Sending to update:'.print_r($data, true));
            $ret =  $this->_ci->obs->update_observation($data, $intProvID);
        }
        return;
    }//saveResponse


    public function isCHF($user, $pid='0') {
        /**
         *
         *	@internal 	Checks if person has CHF checked
         *	@param 		user: user_id
         *	@return 	bool
         *
         */

        $query = <<<query
select u.user_id, i.items_id, u.meta_value 
from rules_ucp u
JOIN rules_items i on i.items_id = u.items_id
JOIN rules_pcp p on p.pcp_id = i.pcp_id and p.prov_id = {$pid}
where i.items_text = 'Monitor Weight Changes for CHF'
and u.user_id = {$user}
query;

// echo $query;

        $results = $this->db->query($query);
        $ret = $results->row();
        return $ret->meta_value;

    }//isCHF


    public function getReadings($provid, $user_id) {
        /**
         *
         *	@internal 	Returns list of today's readings (bp, bs, wt, and cs)
         *	@param 		provid: provider id
         *				user_id: user id
         *	@return 	array of readings found
         *
         */

        $query = <<<query
select o.obs_key, o.obs_value
from ma_{$provid}_observations o
where o.user_id = {$user_id}
and date(o.obs_date) = date(now())
and o.obs_unit = ''
and o.obs_key in ('Blood_Sugar', 'Blood_Pressure', 'Weight', 'Cigarettes')
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->result_array();

    }//getReadings


    public function getReadingDefaults($user_id, $blog_id = 0) {
        /**
         *
         *	@internal 	Returns Default list for today's readings (bp, bs, and wt)
         *	@param 		user_id: user id
         *	@return 	array of readings to check for
         *
         */

        $query = <<<query
select q.obs_key, u.meta_value as cdays, weekday(now())+1 as today,
ifnull(u2.meta_value, 'Inactive') as UActive, ifnull(im2.meta_value, 'Inactive') as APActive, q.msg_id
from rules_questions q
join rules_items i on i.qid = q.qid
join rules_pcp p on p.pcp_id = i.pcp_id
left join rules_items cd on cd.items_parent = i.items_id and cd.items_text = 'Contact Days'
left join rules_ucp u on u.items_id = cd.items_id and u.user_id = {$user_id}
left join rules_ucp u2 on u2.items_id = i.items_id and u2.user_id = {$user_id} and u2.meta_key = 'status' 
left join rules_itemmeta im2 on im2.items_id = i.items_id and im2.meta_key = 'AllPatients' 
where q.obs_key in ('Blood_Sugar', 'Blood_Pressure', 'Weight', 'Cigarettes')
and p.prov_id = {$blog_id}
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->result_array();

    }//getReadingDefaults


    public function getLastWeight($provid, $intID) {
        /**
         *
         *	@internal 	Returns last weight we recieved
         *	@param 		intID: User ID
         *	@return 	question observation date and weight
         *
         */

        $query = <<<query
select date_format(obs_date, '%Y-%m-%d') as obs_date, obs_value
from ma_{$provid}_observations 
where user_id = {$intID}
and obs_key = 'Weight' 
and date_format(obs_date, '%Y-%m-%d') < date_format(now(), '%Y-%m-%d')
and obs_unit = ''
order by obs_date desc
limit 1
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->row();

    }//getLastWeight


    /**
     * @param int $pcp_id
     * @param int $int_blog_id
     * @return mixed
     */
    public function getQuestionIdsByPCP($pcp_id = 2, $int_blog_id = 0) {
        // set blog id
        $this->int_blog_id = $int_blog_id;

        $this->db->select('ri.qid, rq.msg_id, ri.pcp_id');
        $this->db->from('rules_questions AS rq');
        $this->db->join('rules_items AS ri', 'ri.qid = rq.qid');
        $this->db->where('ri.pcp_id', $pcp_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * @param int $item_id
     * @param int $meta_key
     * @return mixed
     */
    public function get_itemmeta_value_by_key($item_id = 2, $meta_key = 0, $int_blog_id = 7) {
        // set blog id
        $this->int_blog_id = $int_blog_id;

        $this->db->select('im.meta_value');
        $this->db->from('rules_itemmeta AS im');
        $this->db->where(array('im.meta_key' => $meta_key, 'im.items_id' => $item_id));
        $query = $this->db->get();
        return $query->row();
    }


    public function getTargetWeight($intID) {
        /**
         *
         *	@internal 	Returns last weight we recieved
         *	@param 		intID: User ID
         *	@return 	question observation date and weight
         *
         */

        $query = <<<query
select p.meta_value, i.items_text
from rules_ucp p
join rules_items i on i.items_id = p.items_id
where i.items_text = 'Target Weight'
and p.user_id = {$intID}
query;

// echo $query;

        $results = $this->db->query($query);

        return $results->row();

    }//getTargetWeight





    public function get_message_id_from_item_id($item_id, $int_blog_id = 0) {
        // set starting defaults
        $target_qid = 0;

        // set blog id
        $this->int_blog_id = $int_blog_id;

        // get result for item (likely a child item)
        $this->db->select('ri.qid, ri.items_parent');
        $this->db->from('rules_items AS ri');
        $this->db->where(array('ri.items_id' => $item_id));
        $query = $this->db->get();
        $result = $query->result_array();
        if(isset($result[0]['qid'])) {
            $target_qid = $result[0]['qid'];
            if($result[0]['qid'] == 0) {
                // if qid=0, query for parent qid
                $this->db->select('ri.qid');
                $this->db->from('rules_items AS ri');
                $this->db->where(array('ri.items_id' => $result[0]['items_parent']));
                $query = $this->db->get();
                $result = $query->result_array();
                if(empty($result)) {
                    $target_qid = '';
                } else if(isset($result[0]['qid'])) {
                    $target_qid = $result[0]['qid'];
                }
            }
        }
        if($target_qid > 0) {
            $this->db->select('rq.msg_id');
            $this->db->from('rules_questions AS rq');
            $this->db->where(array('rq.qid' => $target_qid));
            $query = $this->db->get();
            $result = $query->result_array();
            if(isset($result[0]['msg_id'])) {
                return $result[0]['msg_id'];
            }
        }
        return false;
    }




    public function get_adherence_items($int_blog_id) {
        // get message ids
        $this->db->select('ri.qid, ri.items_id, rq.msg_id, rq.obs_key AS alert_key, rm.meta_value AS alert_msg_id');
        $this->db->from('rules_questions rq');
        $this->db->join('rules_items ri', 'ri.qid = rq.qid');
        $this->db->join('rules_pcp pcp', 'ri.pcp_id = pcp.pcp_id');
        $this->db->join('rules_itemmeta rm', "rm.items_id = ri.items_id AND rm.meta_key = 'alert_msg_id'");
        $this->db->where(array('rq.obs_key' => 'Adherence', 'pcp.prov_id' => $int_blog_id));
        $this->db->order_by("items_id", "desc");
        $query = $this->db->get();
        return $query->result_array();
    }


    public function get_items_by_alert_key($alert_key, $int_blog_id) {
        // set blog id
        $this->int_blog_id = $int_blog_id;

        // build tables to use
        $str_observation_table = 'ma_' . $this->int_blog_id . '_observations';
        $str_observationmeta_table  = 'ma_' . $this->int_blog_id . '_observationmeta';

        $this->db->select('ri.qid, rip.items_id, rq.msg_id, im.meta_value AS alert_key, im_al.meta_value AS alert_msg_id');
        $this->db->from('rules_itemmeta AS im');
        $this->db->join("rules_items AS ri", 'ri.items_id = im.items_id');
        $this->db->join("rules_items AS rip", 'ri.items_parent = rip.items_id');
        $this->db->join("rules_questions AS rq", 'rq.qid = rip.qid');
        $this->db->join("rules_pcp AS pcp", 'ri.pcp_id = pcp.pcp_id');
        $this->db->join('rules_itemmeta im_al', "im_al.items_id = ri.items_id AND im_al.meta_key = 'alert_msg_id'", 'LEFT');
        $this->db->where("im.meta_key = 'alert_key' AND im.meta_value = '" . $alert_key . "' AND prov_id = '" . $int_blog_id . "'");

        $query = $this->db->get();
        return $query->result_array();
    }


    // returns config for user
    public function getUserConfig($provid, $user_id) {
        $sql = "select meta_key, meta_value from wp_usermeta where user_id = {$user_id} and meta_key = 'wp_{$provid}_user_config'";

        $results = $this->db->query($sql);
        $ret = $results->row();
        return $ret->meta_value;

    } //getUserConfig

}
