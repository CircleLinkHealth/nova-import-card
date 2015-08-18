<?php namespace App\Services;

use App\Http\Requests;
use App\WpUser;
use App\Observation;
use App\WpUserMeta;
use Validator;

class CareplanService {

	/**
	 * Get Careplan
	 *
	 * @param  array  $id
	 * @return json
	 */
	public function getCareplan($wpUser, $dates)
	{

		/*
		$msgScheduler = new MsgScheduler;
		$result = $msgScheduler->index('7');
		dd('ITS A WRAPITO!');
		*/
		// start feed
		$feed = array(
			"User_ID" => $wpUser->ID,
			"Comments" => "All data string are variable, DMS quantity and type of messages will change daily for each patient. Messages with Return Responses can nest. Message Content will have variable fields filled in by CPM and can vary between each patient. Message quantities will vary from day to day.",
			"Data" => array(
				"Version" => "2.1",
				"EventDateTime" => date('Y-m-d H:i:s')),
			"CP_Feed" => array(),
		);

		$i = 0;
		// loop through dates
		foreach($dates as $date) {
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
			$feed["CP_Feed"][$i]['Feed']["DMS"] = $this->getObsDMS($wpUser, $date);

			// Reminders
			$feed["CP_Feed"][$i]['Feed']["Reminders"] = $this->getObsReminders($wpUser, $date);

			// Biometric
			$feed["CP_Feed"][$i]['Feed']["Biometric"] = $this->getObsBiometric($wpUser, $date);

			// Symptoms
			$feed["CP_Feed"][$i]['Feed']["Symptoms"] = $this->getObsSymptoms($wpUser, $date);
			$i++;
		}
		return $feed;
	}

	public static function getObsDMS($wpUser, $date)
	{
		$query = Observation::select('ma_' . $wpUser->blogId() . '_observations.*', 'rules_questions.*', 'rules_items.*', 'imsms.meta_value AS sms_en', 'imapp.meta_value AS app_en', 'cm.comment_id', 'cm.comment_parent')
			->join('rules_questions', 'rules_questions.msg_id', '=', 'ma_' . $wpUser->blogId() . '_observations.obs_message_id')
			->join('rules_items', 'rules_items.qid', '=', 'rules_questions.qid')
			->join('rules_itemmeta as imsms', function ($join) {
				$join->on('imsms.items_id', '=', 'rules_items.items_id')->where('imsms.meta_key', '=', 'SMS_EN');
			})
			->leftJoin('rules_itemmeta as imapp', function ($join) {
				$join->on('imapp.items_id', '=', 'rules_items.items_id')->where('imapp.meta_key', '=', 'APP_EN');
			})
			->join('rules_pcp', 'rules_pcp.pcp_id', '=', 'rules_items.pcp_id')
			->join('wp_' . $wpUser->blogId() . '_comments as cm', 'cm.comment_id', '=', 'ma_' . $wpUser->blogId() . '_observations.comment_id')
			->where('ma_' . $wpUser->blogId() . '_observations.user_id', '=', $wpUser->ID)
			->where('obs_unit', '=', 'scheduled')
			->where('prov_id', '=', $wpUser->blogId())
			->whereRaw("obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
			->take(40);
		$scheduledObs = $query->get();
		$dsmObs = array();
		if (!$scheduledObs->isEmpty()) {
			$d = 0;
			foreach ($scheduledObs as $obs) {
				// add to feed
				//$feed["CP_Feed"][$i]['Feed']["DSM"][$d] = array(
				$dsmObs[$d] = array(
					"MessageID" => $obs->obs_message_id,
					"Obs_Key" => $obs->obs_key,
					"ParentID" => $obs->comment_id,
					"MessageIcon" => "question",
					"MessageContent" => $obs->sms_en,
					"ReturnFieldType" => $obs->qtype,
					"ReturnDataRangeLow" => null,
					"ReturnDataRangeHigh" => null,
					"ReturnValidAnswers" => null,
					"PatientAnswer" => null,
					"ResponseDate" => null
				);

				// check for PatientAnswer and ResponseDTS
				$query = Observation::select('o.obs_id', 'o.obs_key', 'o.comment_id', 'o.obs_date', 'o.user_id', 'o.obs_value', 'o.obs_unit', 'o.obs_method', 'o.obs_message_id')
					->from('ma_' . $wpUser->blogId() . '_observations AS o')
					->join('wp_' . $wpUser->blogId() . '_comments AS cm', 'o.comment_id', '=', 'cm.comment_id')
					->where('o.user_id', "=", $wpUser->ID)
					->where('o.obs_key', "=", $obs->obs_key)
					->where('o.obs_message_id', "=", $obs->obs_message_id)
					->where('o.obs_unit', "!=", 'invalid')
					->where('o.obs_unit', "!=", 'scheduled')
					->whereRaw("o.obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
					->orderBy("o.obs_date", "desc");
				$answerObs = $query->first();
				if ($answerObs) {
					$dsmObs[$d]['PatientAnswer'] = '[' . $answerObs->obs_id . ']' . $answerObs->obs_value;
					$dsmObs[$d]['ResponseDate'] = $answerObs->obs_date->format('Y-m-d H:i:s');
				}
				$d++;
			}
		}
		return $dsmObs;
	}


	public static function getObsReminders($wpUser, $date)
	{
		// this only deals with HSP question
		return array();
	}

	public static function getObsBiometric($wpUser, $date)
	{
		// get biometrics that patient has active on their care plan
		return array();
	}

	public static function getObsSymptoms($wpUser, $date)
	{
		// phil is updating the question set to use SYM_51/52/53/54
		return array();
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
