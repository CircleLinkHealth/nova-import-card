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
		// start feed
		$feed = array(
			"User_ID" => $wpUser->ID,
			"Comments" => "All data string are variable, DMS quantity and type of messages will change daily for each patient. Messages with Return Responses can nest. Message Content will have variable fields filled in by CPM and can vary between each patient. Message quantities will vary from day to day.",
			"Data" => array(
				"Version" => "2.1",
				"EventDateTime" => "2015-04-21T15:22:00.472Z"),
			"CP_Feed" => array(),
		);

		$i = 0;
		// loop through dates
		foreach($dates as $date) {
			// instantiate feed for date
			$feed["CP_Feed"][$i] = array(
				"Feed" => array(
					"FeedDate" => $date,
					"DSM" => array(),
					"Reminders" => array(),
					"Biometric" => array(),
					"Symptoms" => array())
			);

			// DSM
			$feed["CP_Feed"][$i]['Feed']["DSM"] = $this->getObsDSM($wpUser, $date);

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

	public static function getObsDSM($wpUser, $date)
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
		if (!$scheduledObs->isEmpty()) {
			$d = 0;
			$dsmObs = array();
			foreach ($scheduledObs as $obs) {
				// add to feed
				//$feed["CP_Feed"][$i]['Feed']["DSM"][$d] = array(
				$dsmObs[$d] = array(
					"MessageID" => $obs->obs_message_id,
					"Obs_Key" => $obs->obs_key,
					"ParentID" => $obs->comment_parent,
					"MesageIcon" => "question",
					"MessageContent" => $obs->sms_en,
					"ReturnFieldType" => "None",
					"ReturnDataRangeLow" => null,
					"ReturnDataRangeHigh" => null,
					"ReturnValidAnswers" => null,
					"PatientAnswer" => null,
					"ResponseDTS" => null
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
					$dsmObs[$d]['ResponseDTS'] = $answerObs->obs_date->format('Y-m-d H:i:s');
				}
				$d++;
			}
		}
		return $dsmObs;
	}


	public static function getObsReminders($wpUser, $date)
	{
		return '';
	}

	public static function getObsBiometric($wpUser, $date)
	{
		return '';
	}

	public static function getObsSymptoms($wpUser, $date)
	{
		return '';
	}

}
