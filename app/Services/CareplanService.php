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


	public function setStateAppForDate($date) {
		// find comment
		$comment = DB::connection('mysql_no_prefix')
			->table('wp_' . $this->programId . '_comments')
			->where('user_id', '=', $this->wpUser->ID)
			->where('comment_type', '=', 'state_app')
			->whereRaw("comment_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
			->first();
		$this->stateAppArray = array();
		if(isset($comment->comment_content)) {
			$this->stateAppCommentId = $comment->comment_ID;
			$this->stateAppArray = unserialize($comment->comment_content);
		}
	}

	private function setObsDMS()
	{
		// get scheduled observations for the day
		$scheduledObservations = $this->getScheduledDMS($this->programId, $this->wpUser->ID, $this->date);

		// build array of ids for matching
		$scheduledObsIds = array();
		if (!empty($scheduledObservations)) {
			foreach ($scheduledObservations as $scheduledObs) {
				$scheduledObsIds[] = $scheduledObs->obs_message_id;
			}
		}

		$dsmObs = array();
		$o = 0;
		foreach($this->stateAppArray as $key => $msgSet) {
			if (in_array(key($msgSet[0]), $scheduledObsIds)) {
				// found a DMS match
				$scheduledObs = $scheduledObservations[key($msgSet[0])];
				// loop through each row of message set
				foreach($msgSet as $i => $msgRow) {
					$msgCPRules = new MsgCPRules;
					$msgSubstitutions = new MsgSubstitutions;
					//obtain message type
					$qsType  = $msgCPRules->getQsType(key($msgRow), $this->wpUser->ID);
					$currQuestionInfo  = $msgCPRules->getQuestion(key($msgRow), $this->wpUser->ID, 'SMS_EN', $this->programId, $qsType);
					$currQuestionInfo->message = $msgSubstitutions->doSubstitutions($currQuestionInfo->message, $this->programId, $this->wpUser->ID);
					// add to feed
					$dsmObs[$o] = array(
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
					$dsmObs[$o]['PatientAnswer'] = $msgRow[key($msgRow)];
					// find answer observation (patient inbound always = observation)
					$answerObs = $this->getAnswerObservation($this->programId, $this->wpUser->ID, $currQuestionInfo->obs_key, $currQuestionInfo->msg_id, $this->date);
					if ($answerObs) {
						// if has answer, add answer and date of answer to feed
						$dsmObs[$o]['PatientAnswer'] = '[' . $answerObs->obs_id . ']' . $answerObs->obs_value;
						$dsmObs[$o]['ResponseDate'] = $answerObs->obs_date;
					}
				}
				$o++;
			}
		}

		return $dsmObs;
	}


	private function setObsReminders()
	{
		// this only deals with HSP question
		return array();
	}

	private function setObsBiometric()
	{
		// get biometrics that patient has active on their care plan
		$bioObs = array();
		$bioObs[] = array(
			"MessageID" => "CF_RPT_50",
			"Obs_Key" => "Cigarettes",
			"ParentID" => $this->stateAppCommentId,
			"MessageIcon" => "cs",
			"MessageContent" => "Please enter the number of cigarettes smoked in the last 24 hours.",
			"ReturnFieldType" => "Range",
			"ReturnDataRangeLow" => "0",
			"ReturnDataRangeHigh" => "120",
			"ReturnValidAnswers" => null,
			"PatientAnswer" => null,
			"ResponseDate" => null
		);
		return $bioObs;
	}

	private function setObsSymptoms()
	{
		// phil is updating the question set to use SYM_51/52/53/54
		return array();
	}













	public function getMessageSequence($msgId) {
		foreach($this->stateAppArray as $key => $msgSet) {
			foreach($msgSet as $i => $msgRow) {
				if (key($msgRow) == $msgId) {
					return $msgSet;
				}
			}
		}
	}



	public function getScheduledDMS($programId, $userId, $date) {
		$query = DB::connection('mysql_no_prefix')->table('ma_' . $programId . '_observations AS o')->select('o.*', 'rules_questions.*', 'rules_items.*', 'imsms.meta_value AS sms_en', 'imapp.meta_value AS app_en', 'cm.comment_id', 'cm.comment_parent')
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
			->whereRaw("o.obs_key IN ('Adherence','Other')")
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



	public function addAppSimCodeToCP($cpFeed) {
		$msgUI = new MsgUI;
		if(!empty($cpFeed['CP_Feed'])) {
			foreach ($cpFeed['CP_Feed'] as $key => $value) {
				$cpFeedSections = array('Biometric', 'DMS', 'Symptoms', 'Reminders');
				foreach ($cpFeedSections as $section) {
					foreach ($cpFeed['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio) {
						$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['formHtml'] = $msgUI->getForm($arrBio, null);
						//echo($msgUI->getForm($arrBio,null));

						if (isset($arrBio['Response'])) {
							//echo($msgUI->getForm($arrBio['Response'],' col-lg-offset-1'));
							$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['formHtml'] = $msgUI->getForm($arrBio['Response'], ' col-lg-offset-1');
							if (isset($arrBio['Response']['Response'])) {
								//echo($msgUI->getForm($arrBio['Response']['Response'],' col-lg-offset-3'));
								$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['Response']['formHtml'] = $msgUI->getForm($arrBio['Response']['Response'], ' col-lg-offset-1');
							}
						}
					}
				}
			}
		}
		return $cpFeed;
	}

}
