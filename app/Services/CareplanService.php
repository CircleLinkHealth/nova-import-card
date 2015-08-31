<?php namespace App\Services;

use App\Http\Requests;
use App\WpUser;
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
			$feed["CP_Feed"][$i]['Feed']["Reminders"] = $this->setObsReminders();

			// Biometric
			$feed["CP_Feed"][$i]['Feed']["Biometric"] = $this->setObsBiometric();

			// Symptoms
			$feed["CP_Feed"][$i]['Feed']["Symptoms"] = $this->setObsSymptoms();
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
		if(!$this->stateAppCommentId) {
			return array();
		}
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

		$dsmObs = array();
		$dsmAdherenceObs = array();
		$dsmAdherenceObs = array();
		$o = 0;
		$msgChooser = new MsgChooser;
		$msgCPRules = new MsgCPRules;
		$msgSubstitutions = new MsgSubstitutions;
		foreach($this->stateAppArray as $key => $msgSet) {
			if (in_array(key($msgSet[0]), $scheduledObsIds)) {
				// found a DMS match
				$scheduledObs = $scheduledObservations[key($msgSet[0])];
				// loop through each row of message set
				foreach($msgSet as $i => $msgRow) {
					//obtain message type
					$qsType  = $msgCPRules->getQsType(key($msgRow), $this->wpUser->ID);
					$currQuestionInfo  = $msgCPRules->getQuestion(key($msgRow), $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
					$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
					// add to feed
					$obsInfo = array(
						"MessageID" => $currQuestionInfo->msg_id,
						"Obs_Key" => $currQuestionInfo->obs_key,
						"ParentID" => $this->stateAppCommentId,
						"MessageIcon" => "question",
						"MessageContent" => $currQuestionInfo->message,
						"ReturnFieldType" => $currQuestionInfo->qtype,
						"ReturnDataRangeLow" => null,
						"ReturnDataRangeHigh" => null,
						"ReturnValidAnswers" => null,
						"PatientAnswer" => null,
						"ResponseDate" => null
					);
					$obsInfo['PatientAnswer'] = $msgRow[key($msgRow)];
					// find answer observation (patient inbound always = observation)
					$answerObs = $this->getAnswerObservation($this->programId, $this->wpUser->ID, $currQuestionInfo->obs_key, $currQuestionInfo->msg_id, $this->date);
					if ($answerObs) {
						// if has answer, add answer and date of answer to feed
						$obsInfo['PatientAnswer'] = $answerObs->obs_value;
						$obsInfo['ResponseDate'] = $answerObs->obs_date;
					}
					if ($i == 0) {
						$dsmObs[$o] = $obsInfo;
					} else if ($i == 1) {
						if (strpos($currQuestionInfo->msg_id,'MED') == false) {
							$dsmObs[$o]['Response'] = $obsInfo;
						}
					} else if ($i == 2) {
						if (strpos($currQuestionInfo->msg_id,'MED') == false) {
							$dsmObs[$o]['Response']['Response'] = $obsInfo;
						}
					}
				}
				$o++;
			} else if (in_array(key($msgSet[0]), $scheduledObsAdherenceIds)) {
				// found a DMS match
				$scheduledObs = $scheduledObservationsAdherence[key($msgSet[0])];
				// loop through each row of message set
				foreach($msgSet as $i => $msgRow) {
					//obtain message type
					$qsType  = $msgCPRules->getQsType(key($msgRow), $this->wpUser->ID);
					$currQuestionInfo  = $msgCPRules->getQuestion(key($msgRow), $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
					$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
					// add to feed
					$obsInfo = array(
						"MessageID" => $currQuestionInfo->msg_id,
						"Obs_Key" => $currQuestionInfo->obs_key,
						"ParentID" => $this->stateAppCommentId,
						"MessageIcon" => "question",
						"MessageContent" => $currQuestionInfo->message,
						"ReturnFieldType" => $currQuestionInfo->qtype,
						"ReturnDataRangeLow" => null,
						"ReturnDataRangeHigh" => null,
						"ReturnValidAnswers" => null,
						"PatientAnswer" => null,
						"ResponseDate" => null
					);
					$obsInfo['PatientAnswer'] = $msgRow[key($msgRow)];
					// find answer observation (patient inbound always = observation)
					$answerObs = $this->getAnswerObservation($this->programId, $this->wpUser->ID, $currQuestionInfo->obs_key, $currQuestionInfo->msg_id, $this->date);
					if ($answerObs) {
						// if has answer, add answer and date of answer to feed
						$obsInfo['PatientAnswer'] = $answerObs->obs_value;
						$obsInfo['ResponseDate'] = $answerObs->obs_date;
					}
					if ($i == 0) {
						$dsmAdherenceObs[$o] = $obsInfo;
					}
				}
				$o++;
			}
		}

		// add in the response to adherence questions (this is the reason for the split weird code above, so that this always comes after the last adherence question)
		$adherenceResponseMsgId = $msgChooser->fxAlgorithmicForApp($this->programId, $this->wpUser->ID, $this->date);
		if(!empty($adherenceResponseMsgId)) {
			$qsType  = $msgCPRules->getQsType($adherenceResponseMsgId, $this->wpUser->ID);
			$currQuestionInfo  = $msgCPRules->getQuestion($adherenceResponseMsgId, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
			$dsmAdherenceObs[$o] = array(
				"MessageID" => $currQuestionInfo->msg_id,
				"Obs_Key" => $currQuestionInfo->obs_key,
				"ParentID" => $this->stateAppCommentId,
				"MessageIcon" => "info",
				"MessageContent" => 'Adherence Result Msg: '.$currQuestionInfo->message,
				"ReturnFieldType" => $currQuestionInfo->qtype,
				"ReturnDataRangeLow" => null,
				"ReturnDataRangeHigh" => null,
				"ReturnValidAnswers" => null,
				"PatientAnswer" => null,
				"ResponseDate" => null
			);
		}

		return array_merge($dsmObs, $dsmAdherenceObs);
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
		// currently on hold
		return array();
	}


	/**
	 * @return array
     */
	private function setObsBiometric()
	{
		if(!$this->stateAppCommentId) {
			return array();
		}
		$msgCPRules = new MsgCPRules;
		$msgSubstitutions = new MsgSubstitutions;
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
		$o = 0;
		foreach($bioMsgIds as $bioMsgId) {
			$matchFound = false;
			foreach($this->stateAppArray as $key => $msgSet) {
				if (key($msgSet[0]) == $bioMsgId) {
					// found a BIO match
					$matchFound = true;
					// loop through each row of message set
					foreach ($msgSet as $i => $msgRow) {
						//obtain message type
						$qsType = $msgCPRules->getQsType(key($msgRow), $this->wpUser->ID);
						$currQuestionInfo = $msgCPRules->getQuestion(key($msgRow), $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
						$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
						// add to feed
						$bioObsTemp = array(
							"MessageID" => $currQuestionInfo->msg_id,
							"Obs_Key" => $currQuestionInfo->obs_key,
							"ParentID" => $this->stateAppCommentId,
							"MessageIcon" => "question",
							"MessageContent" => $currQuestionInfo->message,
							"ReturnFieldType" => $currQuestionInfo->qtype,
							"ReturnDataRangeLow" => null,
							"ReturnDataRangeHigh" => null,
							"ReturnValidAnswers" => null,
							"PatientAnswer" => null,
							"ResponseDate" => null
						);
						$bioObsTemp['PatientAnswer'] = $msgRow[key($msgRow)];
						// find answer observation (patient inbound always = observation)
						$answerObs = $this->getAnswerObservation($this->programId, $this->wpUser->ID, $currQuestionInfo->obs_key, $currQuestionInfo->msg_id, $this->date);
						if ($answerObs) {
							// if has answer, add answer and date of answer to feed
							$bioObsTemp['PatientAnswer'] = $answerObs->obs_value;
							$bioObsTemp['ResponseDate'] = $answerObs->obs_date;
						}
						if($i == 0) {
							$bioObs[$o] = $bioObsTemp;
						} else if($i == 1) {
							$bioObs[$o]['Response'] = $bioObsTemp;
						} else if($i == 2) {
							$bioObs[$o]['Response']['Response'] = $bioObsTemp;
						}
					}
					$o++; // +1 bioObs
				}
			}
			if(!$matchFound) {
				// not yet in state_app, so add just the base question
				//obtain message type
				$qsType = $msgCPRules->getQsType($bioMsgId, $this->wpUser->ID);
				$currQuestionInfo = $msgCPRules->getQuestion($bioMsgId, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
				$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
				// add to feed
				$bioObs[$o] = array(
					"MessageID" => $currQuestionInfo->msg_id,
					"Obs_Key" => $currQuestionInfo->obs_key,
					"ParentID" => $this->stateAppCommentId,
					"MessageIcon" => "question",
					"MessageContent" => $currQuestionInfo->message,
					"ReturnFieldType" => $currQuestionInfo->qtype,
					"ReturnDataRangeLow" => null,
					"ReturnDataRangeHigh" => null,
					"ReturnValidAnswers" => null,
					"PatientAnswer" => null,
					"ResponseDate" => null
				);
				$o++; // +1 bioObs
			}
		}
		return $bioObs;
	}


	/**
	 * @return array
     */
	private function setObsSymptoms()
	{
		if(!$this->stateAppCommentId) {
			return array();
		}
		$msgCPRules = new MsgCPRules;
		$msgSubstitutions = new MsgSubstitutions;
		$msgUser = new MsgUser;
		$symMsgIds = array();
		// not yet in state app
		$scheduledSymptoms = $this->getScheduledSymptoms();
		$symMsgIds = array();
		if(!empty($scheduledSymptoms)) {
			foreach($scheduledSymptoms as $scheduledSym) {
				$symMsgIds[] = $scheduledSym->msg_id;
			}
		}
		$symObs = array();
		$o = 0;
		foreach($symMsgIds as $symMsgId) {
			$matchFound = false;
			foreach($this->stateAppArray as $key => $msgSet) {
				if (key($msgSet[0]) == $symMsgId) {
					// found a SYM match
					$matchFound = true;
					// loop through each row of message set
					foreach ($msgSet as $i => $msgRow) {
						//obtain message type
						$qsType = $msgCPRules->getQsType(key($msgRow), $this->wpUser->ID);
						$currQuestionInfo = $msgCPRules->getQuestion(key($msgRow), $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
						$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
						// add to feed
						$symObsTemp = array(
							"MessageID" => $currQuestionInfo->msg_id,
							"Obs_Key" => $currQuestionInfo->obs_key,
							"ParentID" => $this->stateAppCommentId,
							"MessageIcon" => "question",
							"MessageContent" => $currQuestionInfo->message,
							"ReturnFieldType" => $currQuestionInfo->qtype,
							"ReturnDataRangeLow" => null,
							"ReturnDataRangeHigh" => null,
							"ReturnValidAnswers" => null,
							"PatientAnswer" => null,
							"ResponseDate" => null
						);
						$symObsTemp['PatientAnswer'] = $msgRow[key($msgRow)];
						// find answer observation (patient inbound always = observation)
						$answerObs = $this->getAnswerObservation($this->programId, $this->wpUser->ID, $currQuestionInfo->obs_key, $currQuestionInfo->msg_id, $this->date);
						if ($answerObs) {
							// if has answer, add answer and date of answer to feed
							$symObsTemp['PatientAnswer'] = $answerObs->obs_value;
							$symObsTemp['ResponseDate'] = $answerObs->obs_date;
						}
						if($i == 0) {
							$symObs[$o] = $symObsTemp;
						} else if($i == 1) {
							$symObs[$o]['Response'] = $symObsTemp;
						} else if($i == 2) {
							$symObs[$o]['Response']['Response'] = $symObsTemp;
						}
					}
					$o++; // +1 symObs
				}
			}
			if(!$matchFound) {
				// not yet in state_app, so add just the base question
				//obtain message type
				$qsType = $msgCPRules->getQsType($symMsgId, $this->wpUser->ID);
				$currQuestionInfo = $msgCPRules->getQuestion($symMsgId, $this->wpUser->ID, $this->msgLanguageType, $this->programId, $qsType);
				$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
				// add to feed
				$symObs[$o] = array(
					"MessageID" => $currQuestionInfo->msg_id,
					"Obs_Key" => $currQuestionInfo->obs_key,
					"ParentID" => $this->stateAppCommentId,
					"MessageIcon" => "question",
					"MessageContent" => $currQuestionInfo->message,
					"ReturnFieldType" => $currQuestionInfo->qtype,
					"ReturnDataRangeLow" => null,
					"ReturnDataRangeHigh" => null,
					"ReturnValidAnswers" => null,
					"PatientAnswer" => null,
					"ResponseDate" => null
				);
				$o++; // +1 symObs
			}
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
			->join('wp_' . $programId . '_comments as cm', 'cm.comment_id', '=', 'o.comment_id')
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
		// query
		$query = DB::connection('mysql_no_prefix')->table('rules_ucp AS rucp');
		$query->select('rucp.*', 'rq.*', 'pcp.pcp_id', 'pcp.section_text', 'i.qid', 'i.items_parent', 'i.items_id', 'i.items_text', 'rq.msg_id', 'ims.meta_value AS ui_sort', 'rucp.meta_value as status', 'rip.qid AS items_parent_qid', 'rqp.msg_id AS items_parent_msg_id', 'imsms.meta_value AS sms_en', 'imapp.meta_value AS app_en');
		$query->where('user_id', '=', $this->wpUser->ID);
		$query->join('rules_items AS i', 'i.items_id', '=', 'rucp.items_id');
		$query->leftJoin('rules_items AS rip', 'i.items_parent', '=', 'rip.items_id'); // parent item info
		$query->join('rules_pcp AS pcp', function ($join) {
			$join->on('i.pcp_id', '=', 'pcp.pcp_id')->where('pcp.prov_id', '=', $this->programId);
		});

		$query->leftJoin('rules_itemmeta as imsms', function ($join) {
			$join->on('imsms.items_id', '=', 'i.items_id')->where('imsms.meta_key', '=', 'SMS_EN');
		});
		$query->leftJoin('rules_itemmeta as imapp', function ($join) {
				$join->on('imapp.items_id', '=', 'i.items_id')->where('imapp.meta_key', '=', 'APP_EN');
			});
		$query->leftJoin('rules_questions AS rq', 'rq.qid', '=', 'i.qid');
		$query->leftJoin('rules_questions AS rqp', 'rqp.qid', '=', 'rip.qid'); // parent question info
		$query->leftJoin('rules_itemmeta AS ims', function ($join) {
			$join->on('ims.items_id', '=', 'i.items_id')->where('ims.meta_key', '=', 'ui_sort');
		});
		$query->whereRaw("(rucp.meta_key = 'status' OR rucp.meta_key = 'value') AND user_id = " . $this->wpUser->ID);
		//$query->where('rq.obs_key', '=', 'Severity');
		$query->where('rucp.meta_value', '=', 'Active');
		$query->where('i.pcp_id', '=', '5');
		$query->orderBy("ui_sort", 'ASC');
		$query->orderBy("i.items_id", 'DESC');
		$result = $query->get();

		//dd($query->toSql());

		$arrReturnResult = array();
		// set alert_values
		if(!empty($result)) {
			foreach ($result as $row) {
				$arrReturnResult[$row->msg_id] = $row;
			}
		}
		return $arrReturnResult;
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
