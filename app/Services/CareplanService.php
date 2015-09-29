<?php namespace App\Services;

use App\CPRulesQuestions;
use App\Http\Requests;
use App\WpUser;
use App\CPRulesUCP;
use App\Observation;
use App\WpUserMeta;
use App\Comment;
use DB;
use Validator;

class CareplanService {

	var $wpUser; // user model
	var $programId;
	var $msgLanguageType = 'APP_EN';
	var $date; // date yyyy-mm-dd
	var $stateAppArray = array(); // array of state_app comment_content
	var $stateAppCommentId;

	/**
	 * Get Careplan
	 *
	 * @param  array  $wpUser
	 * @param  array  $dates
	 * @return array
	 */
	public function getCareplan($wpUser, $dates)
	{
		// set universal user / vars
		$this->wpUser = $wpUser;
		$this->programId = $wpUser->blogId();

		// start feed
		$feed = array(
			"User_ID" => $this->wpUser->ID,
			"Comments" => "All data string are variable, DMS quantity and type of messages will change daily for each patient. Messages with Return Responses can nest. Message Content will have variable fields filled in by CPM and can vary between each patient. Message quantities will vary from day to day.",
			"Data" => array(
				"Version" => "2.1",
				"EventDateTime" => date('Y-m-d H:i:s')),
			"CP_Feed" => array(),
		);

		$i = 0;
		// loop through dates
		foreach($dates as $date) {
			// set date
			$this->date = $date;

			// set stateApp for date
			$this->setStateAppForDate($date);

			// instantiate feed for date
			$feed["CP_Feed"][$i] = array(
				"Feed" => array(
					"FeedDate" => $date,
					"Messages" => array(),
					"DMS" => array(),
					"Reminders" => array(),
					"Biometric" => array(),
					"Symptoms" => array())
			);

			// DSM
			$feed["CP_Feed"][$i]['Feed']["DMS"] = $this->setObsDMS();

			// Reminders
			// check if user has HSP scheduled for today
			// based on existance of state_hsp_dm comment record
			$stateHsp = Comment::where('comment_type', '=', 'state_hsp_dm')
				->where("user_id", "=", $this->wpUser->ID)
				->whereRaw("comment_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
				->first();
			if($stateHsp) {
				$feed["CP_Feed"][$i]['Feed']["Reminders"] = array(
					0 => array(
						"MessageID" => "CF_DM_HSP_10",
						"Obs_Key" => "HSP_DM",
						"ParentID" => "604",
						"MessageIcon" => "hsp",
						"MessageCategory" => "Hospital",
						"MessageContent" => "Are you Currently in the Hospital or ER?",
						"ReturnFieldType" => '',
						"ReturnDataRangeLow" => '',
						"ReturnDataRangeHigh" => '',
						"ReturnValidAnswers" => '',
						"PatientAnswer" => '',
						"ResponseDate" => '',
						"Response" => $this->setObsReminders())
				);
			}

			// Biometric
			$feed["CP_Feed"][$i]['Feed']["Biometric"] = $this->setObsBiometric();

			// Symptoms
			$scheduledsyms = $this->setObsSymptoms();
			if(!empty($scheduledsyms)) {
				$feed["CP_Feed"][$i]['Feed']["Symptoms"] = array(
					0 => array(
						"MessageID" => "CF_SYM_MNU_10",
						"Obs_Key" => "Severity",
						"ParentID" => "603",
						"MessageIcon" => "question",
						"MessageCategory" => "Question",
						"MessageContent" => "Any symptoms today",
						"ReturnFieldType" => '',
						"ReturnDataRangeLow" => '',
						"ReturnDataRangeHigh" => '',
						"ReturnValidAnswers" => '',
						"PatientAnswer" => '',
						"ResponseDate" => '',
						"Response" => $this->setObsSymptoms())
				);
			}
			$i++;
		}
		return $feed;
	}

	/**
	 * @param $date
     */
	public function setStateAppForDate($date) {
		$this->stateAppCommentId = false;
		// find comment
		$comment = DB::connection('mysql_no_prefix')
			->table('lv_comments')
			->where('user_id', '=', $this->wpUser->ID)
			->where('comment_type', '=', 'state_app')
			->whereRaw("comment_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
			->first();
		$this->stateAppArray = array();
		if(isset($comment->comment_content)) {
			$this->stateAppCommentId = $comment->id;
			$this->stateAppArray = unserialize($comment->comment_content);
		}
	}

	/**
	 * @return array
     */
	private function setObsDMS()
	{
		$msgCPRules = new MsgCPRules;
		$msgChooser = new MsgChooser;
		// get scheduled observations for the day
		$scheduledObservations = $this->getScheduledDMS($this->programId, $this->wpUser->ID, $this->date, 'Other');
		$scheduledObservationsAdherence = $this->getScheduledDMS($this->programId, $this->wpUser->ID, $this->date, 'Adherence');

		// build array of ids for matching
		$scheduledObsIds = array();
		if (!empty($scheduledObservations)) {
			foreach ($scheduledObservations as $scheduledObs) {
				$scheduledObsIds[] = $scheduledObs->obs_message_id;
			}
		}

		// build array of ids for matching
		$scheduledObsAdherenceIds = array();
		if (!empty($scheduledObservationsAdherence)) {
			foreach ($scheduledObservationsAdherence as $scheduledObsAdherence) {
				$scheduledObsAdherenceIds[] = $scheduledObsAdherence->obs_message_id;
			}
		}

		$scheduledObsIds = array_merge($scheduledObsIds, $scheduledObsAdherenceIds);

		$sectionObs = array();
		$i = 0;
		foreach($scheduledObsIds as $obsMsgId) {
			$observation = Observation::where('obs_message_id', '=', $obsMsgId)
				->where('user_id', '=', $this->wpUser->ID)
				->where('obs_unit', '!=', 'scheduled')
				->where('obs_unit', '!=', 'invalid')
				->where('obs_unit', '!=', 'outbound')
				->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
				->orderBy('obs_date', 'desc')
				->first();
			if(!empty($observation) && $observation->comment_id != 0) {
				$sectionObs[$i] = $this->renderCommentThread($obsMsgId, $observation->comment_id);
			} else {
				$sectionObs[$i] = $this->renderCommentThread($obsMsgId, 0);
			}
			$i++;
		}

		/*
		// add in the response to adherence questions (this is the reason for the split weird code above, so that this always comes after the last adherence question)
		$dsmAdherenceObs = array();
		$adherenceResponseMsgId = $msgChooser->fxAlgorithmicForApp($this->programId, $this->wpUser->ID, $this->date);
		if(!empty($adherenceResponseMsgId)) {
			$qsType  = $msgCPRules->getQsType($adherenceResponseMsgId, $this->wpUser->ID);
			$currQuestionInfo  = $msgCPRules->getQuestion($adherenceResponseMsgId, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
			$dsmAdherenceObs[0] = array(
				"MessageID" => $currQuestionInfo->msg_id,
				"Obs_Key" => $currQuestionInfo->obs_key,
				"ParentID" => $this->stateAppCommentId,
				"MessageIcon" => $currQuestionInfo->icon,
				"MessageCategory" => $currQuestionInfo->category,
				"MessageContent" => ''.$currQuestionInfo->message,
				"ReturnFieldType" => $currQuestionInfo->qtype,
				"ReturnDataRangeLow" => $currQuestionInfo->low,
				"ReturnDataRangeHigh" => $currQuestionInfo->high,
				"ReturnValidAnswers" => '',
				"PatientAnswer" => '',
				"ResponseDate" => ''
			);
		}
		*/
		return $sectionObs;
	}


	/**
	 * @return array
     */
	private function setObsReminders()
	{
		if(!$this->stateAppCommentId) {
			return array();
		}
		// this only deals with HSP question
		//$hspMsg = CPRulesQuestions::where('obs_key', '=', 'HSP')->first();
		$hspMsgIds = array('CF_HSP_20', 'CF_HSP_30');
		$hspObs = array();
		// look for $hspMsgId
		$i = 0;
		foreach($hspMsgIds as $hspMsgId) {
			$observation = Observation::where('obs_message_id', '=', $hspMsgId)
				->where('user_id', '=', $this->wpUser->ID)
				->where('obs_unit', '!=', 'scheduled')
				->where('obs_unit', '!=', 'invalid')
				->where('obs_unit', '!=', 'outbound')
				->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
				->orderBy('obs_date', 'desc')
				->first();
			if(!empty($observation) && $observation->comment_id != 0) {
				$hspObs[$i] = $this->renderCommentThread($hspMsgId, $observation->comment_id);
			} else {
				$hspObs[$i] = $this->renderCommentThread($hspMsgId, 0);
			}
			$i++;
		}
		return $hspObs;
	}


	/**
	 * @return array
     */
	private function setObsBiometric()
	{
		$msgUser = new MsgUser;
		$bioMsgIds = array();
		$userInfo = $msgUser->get_users_data($this->wpUser->ID, 'id', $this->programId, true);
		if (!empty($userInfo[$this->wpUser->ID]['usermeta']['user_care_plan'])) {
			$userInfoUCP = $userInfo[$this->wpUser->ID]['usermeta']['user_care_plan'];
			// loop through care plan
			foreach($userInfoUCP as $obsKey => $ucpItem) {
				if(isset($ucpItem['parent_status'])) {
					// if active
					if ($ucpItem['parent_status'] == 'Active') {
						$query = DB::connection('mysql_no_prefix')->table('rules_questions')->select('msg_id')
							->where('obs_key', "=", $obsKey);
						$questionInfo = $query->first();
						if ($questionInfo) {
							$msgId = $questionInfo->msg_id;
							$bioMsgIds[] = $msgId;
						}
					}
				}
			}
		}
		$bioObs = array();
		$i = 0;
		foreach($bioMsgIds as $bioMsgId) {
			$observation = Observation::where('obs_message_id', '=', $bioMsgId)
				->where('user_id', '=', $this->wpUser->ID)
				->where('obs_unit', '!=', 'scheduled')
				->where('obs_unit', '!=', 'invalid')
				->where('obs_unit', '!=', 'outbound')
				->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
				->orderBy('obs_date', 'desc')
				->first();
			if(!empty($observation) && $observation->comment_id != 0) {
				$bioObs[$i] = $this->renderCommentThread($bioMsgId, $observation->comment_id);
			} else {
				$bioObs[$i] = $this->renderCommentThread($bioMsgId, 0);
			}
			$i++;
		}
		return $bioObs;
	}

	private function renderCommentThread($msgId, $commentId = 0) {
		$msgCPRules = new MsgCPRules;
		$msgSubstitutions = new MsgSubstitutions;
		// for unanswered:
		if(empty($commentId)) {
			$qsType = $msgCPRules->getQsType($msgId, $this->wpUser->ID);
			$currQuestionInfo = $msgCPRules->getQuestion($msgId, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
			$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
			//echo $msgId .'-'. $this->wpUser->ID .'-'. $this->msgLanguageType .'-'. $this->programId .'-'. $qsType."<br><BR>".PHP_EOL;
			// add to feed
			$obsArr = array(
				"MessageID" => $currQuestionInfo->msg_id,
				"Obs_Key" => $currQuestionInfo->obs_key,
				"ParentID" => 0,
				"MessageIcon" => $currQuestionInfo->icon,
				"MessageCategory" => $currQuestionInfo->category,
				"MessageContent" => $currQuestionInfo->message,
				"ReturnFieldType" => $currQuestionInfo->qtype,
				"ReturnDataRangeLow" => $currQuestionInfo->low,
				"ReturnDataRangeHigh" => $currQuestionInfo->high,
				"ReturnValidAnswers" => '',
				"PatientAnswer" => '',
				"ResponseDate" => ''
			);
			return $obsArr;
		}
		// get all observations for message_thread
		$observations = Observation::where('comment_id', '=', $commentId)->orderBy('sequence_id', 'asc')->get();
		//dd($observations);
		$obsArr = array();
		if($observations->count() > 0) {
			$o = 0;
			foreach($observations as $observation) {
				//obtain message type

				$qsType = $msgCPRules->getQsType($observation->obs_message_id, $this->wpUser->ID);
				$currQuestionInfo = $msgCPRules->getQuestion($observation->obs_message_id, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
				if($currQuestionInfo) {
					if (isset($currQuestionInfo->message)) {
						$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
					} else {
						$currQuestionInfo->message = '-';
					}
					// add to feed
					$obsTemp = array(
						"MessageID" => $currQuestionInfo->msg_id,
						"Obs_Key" => $currQuestionInfo->obs_key,
						"ParentID" => 0,
						"MessageIcon" => $currQuestionInfo->icon,
						"MessageCategory" => $currQuestionInfo->category,
						"MessageContent" => $currQuestionInfo->message,
						"ReturnFieldType" => $currQuestionInfo->qtype,
						"ReturnDataRangeLow" => $currQuestionInfo->low,
						"ReturnDataRangeHigh" => $currQuestionInfo->high,
						"ReturnValidAnswers" => '',
						"PatientAnswer" => $observation->obs_value,
						"ResponseDate" => $observation->obs_date
					);
					if ($o == 0) {
						$obsArr = $obsTemp;
					} else if ($o == 1) {
						$obsArr['Response'][0] = $obsTemp;
					} else if ($o == 2) {
						$obsArr['Response']['Response'][0] = $obsTemp;
					}
				}
				$o++; // +1 bioObs
			}
		}
		return $obsArr;
	}


	/**
	 * @return array
     */
	private function setObsSymptoms()
	{
		$symMsgIds = $this->getScheduledSymptoms();
		$symObs = array();
		$i = 0;
		foreach($symMsgIds as $symMsgId) {
			$observation = Observation::where('obs_message_id', '=', $symMsgId)
				->where('user_id', '=', $this->wpUser->ID)
				->where('obs_unit', '!=', 'scheduled')
				->where('obs_unit', '!=', 'invalid')
				->where('obs_unit', '!=', 'outbound')
				->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
				->orderBy('obs_date', 'desc')
				->first();
			if(!empty($observation) && $observation->comment_id != 0) {
				$symObs[$i] = $this->renderCommentThread($symMsgId, $observation->comment_id);
			} else {
				$symObs[$i] = $this->renderCommentThread($symMsgId, 0);
			}
			$i++;
		}
		return $symObs;
	}

	/**
	 * @param $programId
	 * @param $userId
	 * @param $date
	 * @param $obsKey
	 * @return array
     */
	public function getScheduledDMS($programId, $userId, $date, $obsKey) {
		$query = DB::connection('mysql_no_prefix')->table('lv_observations AS o')->select('o.*', 'rules_questions.*', 'rules_items.*', 'imsms.meta_value AS sms_en', 'imapp.meta_value AS app_en')
			->join('rules_questions', 'rules_questions.msg_id', '=', 'o.obs_message_id')
			->join('rules_items', 'rules_items.qid', '=', 'rules_questions.qid')
			->join('rules_itemmeta as imsms', function ($join) {
				$join->on('imsms.items_id', '=', 'rules_items.items_id')->where('imsms.meta_key', '=', 'SMS_EN');
			})
			->leftJoin('rules_itemmeta as imapp', function ($join) {
				$join->on('imapp.items_id', '=', 'rules_items.items_id')->where('imapp.meta_key', '=', 'APP_EN');
			})
			->join('rules_pcp', 'rules_pcp.pcp_id', '=', 'rules_items.pcp_id')
			->join('lv_comments as cm', 'cm.id', '=', 'o.comment_id')
			->where('o.user_id', '=', $userId)
			->where('obs_unit', '=', 'scheduled')
			->whereRaw("o.obs_key IN ('$obsKey')")
			->whereRaw("o.obs_message_id NOT IN ('CF_TOD_Q','CF_SOL_EX_01', 'CF_REM_NAG_01', 'CF_SOL_HELO_01')")
			->where('prov_id', '=', $programId)
			->whereRaw("obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
			->take(40);
		$scheduledObservations = $query->get();
		$scheduledObs = array();
		if(!empty($scheduledObservations)) {
			foreach($scheduledObservations as $obs) {
				$scheduledObs[$obs->obs_message_id] = $obs;
			}
		}
		return $scheduledObs;
	}


	/**
	 * @return array
     */
	public function getScheduledSymptoms() {
		$ucp = CPRulesUCP::where('user_id', '=', $this->wpUser->ID)
			->whereHas('item', function($q){
				$q->whereHas('pcp', function($q2){
					$q2->where('section_text', '=', 'Symptoms to Monitor');
				});
			})
			->where('meta_value', '=', 'Active')
			->get();
		if($ucp->count() > 0) {
			foreach($ucp as $ucpItem) {
				//dd($ucpItem->item);
				$msgIds[] = $ucpItem->item->question->msg_id;
			}
		}
		//dd($msgIds);
		return $msgIds;
	}


	/**
	 * @param $programId
	 * @param $userId
	 * @param $obsKey
	 * @param $msgId
	 * @param $date
	 * @return mixed
     */
	public function getAnswerObservation($programId, $userId, $obsKey, $msgId, $date) {
		// find answer observation (patient inbound always = observation)
		$query = DB::connection('mysql_no_prefix')->table('ma_' . $programId . '_observations')->select('o.obs_id', 'o.obs_key', 'o.comment_id', 'o.obs_date', 'o.user_id', 'o.obs_value', 'o.obs_unit', 'o.obs_method', 'o.obs_message_id')
			->from('ma_' . $programId . '_observations AS o')
			->join('wp_' . $programId . '_comments AS cm', 'o.comment_id', '=', 'cm.comment_id')
			->where('o.user_id', "=", $userId)
			->where('o.obs_key', "=", $obsKey)
			->where('o.obs_message_id', "=", $msgId)
			->where('o.obs_unit', "!=", 'invalid')
			->where('o.obs_unit', "!=", 'scheduled')
			->whereRaw("o.obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
			->orderBy("o.obs_date", "desc");
		$answerObs = $query->first();
		return $answerObs;
	}

}
