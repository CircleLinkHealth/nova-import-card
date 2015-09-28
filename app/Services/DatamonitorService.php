<?php namespace App\Services;

use App\CPRulesQuestions;
use App\Http\Requests;
use App\WpUser;
use App\Observation;
use App\ObservationMeta;
use App\WpUserMeta;
use App\Comment;
use DateTime;
use DB;
use Validator;

class DatamonitorService {

	public function __construct() {
		/*
		$this->CI =& get_instance();
		$this->CI->load->database();
		$this->CI->load->model('cpm_1_7_datamonitor_model','datamonitor_model');
		$this->CI->load->model('cpm_1_7_users_model','users_model');
		$this->CI->load->model('cpm_1_7_smsdelivery_model','mailman');
		$this->CI->load->model('cpm_1_7_observation_model','observation_model');
		$this->CI->load->model('cpm_1_7_observationmeta_model','observationmeta_model');
		$this->CI->load->model('cpm_1_7_comments_model','comments_model');
		$this->CI->load->model('cpm_1_7_rules_model','rules_model');
		$this->CI->load->model('cpm_1_7_alerts_model','alerts_model');
		$this->CI->load->library('cpm_1_7_obsprocessor_library');
		$this->CI->load->library('email');
		$this->CI->load->library('session');
		$this->CI->load->library('curl');
		$this->CI->load->library('tinyurl');
		*/

		$this->time_start = 0;
		$this->time_end = 0;
		$this->int_blog_id = 0;
	}

	/**
	 * Summary: run_process_3_day_missed_meds - search for active patients who missed
	 * med 3 days in a row, and if so trigger alert
	 *
	 * @param $int_blog_id
	 * @return string
	 */
	public function run_process_3_day_missed_observations($alert_key, $int_blog_id)
	{
		$this->time_start = microtime(true);
		// set blog id
		$this->int_blog_id = $int_blog_id;

		$log_string = '';
		// get message ids
		$adherence_items = $this->CI->rules_model->get_adherence_items($this->int_blog_id);
		//process
		$log_string .= $this->process_alert_3_day_missed_items('Adherence', $adherence_items);

		return $log_string;
	}

	/**
	 * Summary: run_process_3_day_missed_biometrics - search for active patients who missed
	 * biometric 3 days in a row, and if so trigger alert
	 *
	 *
	 * @param $int_blog_id
	 * @return string
	 */
	public function run_process_3_day_missed_biometrics($alert_key, $int_blog_id)
	{
		$this->time_start = microtime(true);
		// set blog id
		$this->int_blog_id = $int_blog_id;

		$log_string = '';
		// get message ids
		$items = $this->CI->rules_model->get_items_by_alert_key($alert_key, $this->int_blog_id);

		// harcode in alert message ids
		$items_processed = array();
		foreach($items as $item) {
			switch ($alert_key) {
				case 'Blood_Pressure':
					$item['alert_msg_id'] = 'CF_AL_09';
					break;
				case 'Blood_Sugar':
					$item['alert_msg_id'] = 'CF_AL_10';
					break;
				case 'Weight':
					$item['alert_msg_id'] = 'CF_AL_11';
					break;
				case 'Cigarettes':
					$item['alert_msg_id'] = 'CF_AL_12';
					break;
				default:
					break;
			}
			$items_processed[] = $item;
		}
		//echo "<pre>";var_dump($items_processed);echo "</pre>";//die();

		// process
		$log_string .= $this->process_alert_3_day_missed_items($alert_key, $items_processed);

		return $log_string;
	}


	/**
	 * @param $type - the type Adherence, Blood_Pressure, ect
	 * @param $items - rules_ucp
	 * @return string
	 */
	public function process_alert_3_day_missed_items($type, $items) {
		$log_string = '';
		if(!empty($items)) {
			foreach ($items as $item) {
				// get all active users and loop through them
				$active_users = $this->CI->users_model->get_users_for_active_item($item['items_id'], $this->int_blog_id);
				//echo "<pre>";var_dump($active_users);echo "</pre>";die();
				if(!empty($active_users)) {
					foreach($active_users as $active_user) {
						$user_id = $active_user['user_id'];
						$item_obs_ids = '';
						$observation = false;
						$msg_id = $item['msg_id'];
						$day1_date = date('Y-m-d',strtotime("-1 days"));
						$item_observations = $this->CI->observation_model->get_3day_observations(strtolower($item['alert_key']), $msg_id, $user_id, $day1_date, $this->int_blog_id);
						//echo "<pre>";var_dump($item_observations);echo "</pre>";
						if(!empty($item_observations)) {
							$i=1; // standard loop counter, 1 = most recent obs found
							$f=0; // the number of found observations (3 should always be found)
							$dates_processed = array();
							foreach($item_observations as $item_obs) {
								//echo "<pre>";var_dump($item_obs);echo "</pre>";//die();
								$obs_date = $date = date('Y-m-d', strtotime($item_obs['obs_date']));
								// prevent more than one per day being processed (desc so most recent gets first)
								if(in_array($obs_date, $dates_processed)) {
									continue 1;
								} else {
									$dates_processed[] = $obs_date;
								}
								// add NR if blank
								if($item_obs['obs_value'] == '') {
									$item_obs['obs_value'] = 'NR';
								}
								if($i <= 3) {
									// if not already alert, and is blank or "N", tally towards alert
									if(empty($item_obs['dm_log_missed_' . strtolower($item['alert_key'])]) && ($item_obs['obs_value'] == '' || strtolower($item_obs['obs_value']) == 'n' || strtolower($item_obs['obs_value']) == 'nr')){
										//$log_string .= "Patient[{$user_id}][{$msg_id}][{$item_obs['obs_date']}] missed med, obs_value=" .$item_obs['obs_value'] . PHP_EOL;
										$send_alert = true;
										$item_obs_ids .= "(".$item_obs['obs_id']."[{$item_obs['obs_date']}][{$item_obs['obs_value']}])";
										$f++;
										if($i == 1) {
											$observation = $item_obs; // this will store the first obs
										}
									} else {
										if(!empty($item_obs['dm_log_missed_' . strtolower($item['alert_key'])])) {
											$item_obs['obs_value'] = 'ALREADY ALERTED';
										}
										$item_obs_ids .= "(".$item_obs['obs_id']."[{$item_obs['obs_date']}][{$item_obs['obs_value']}])";
									}
									$i++;
								}
							}
						}
						if(!empty($send_alert) && ($f==3) && $observation) {
							// fire alert on first day obs
							$label = 'danger';
							$extra_vars['alert_key'] = $item['alert_key'];
							$message_id = $item['alert_msg_id']; // missed meds are CF_AL_13 through CF-AL-21
							if(!$item['alert_msg_id']) {
								$message_id = '';
							}
							$send_email = false;
							$send_alert = "Patient[{$user_id}][{$msg_id}][ALERT] missed 3 days in a row, checked obs[{$item_obs_ids}]" . PHP_EOL;
							$log_string .= $send_alert;
							// add obsmeta dm_log_missed_' . strtolower($item['alert_key'])
							$observationmeta_paramaters = array(
								'obs_id' => $observation['obs_id'],
								'comment_id' => $observation['comment_id'],
								'message_id' => $message_id,
								'meta_key' => 'dm_log_missed_' . strtolower($item['alert_key']),
								'meta_value' => $send_alert
							);
							$observationmeta_id = $this->CI->observationmeta_model->insert_observationmeta($observationmeta_paramaters, $this->int_blog_id);
							$log_string .= "added new observationmeta dm_log_missed_" . strtolower($item['alert_key']) . " - obsmeta_id = {$observationmeta_id}" . PHP_EOL;
							if($type == 'Adherence') { // HACK FIX
								// update observation to ensure it has obs_key
								$observation_paramaters = array(
									'obs_id' => $observation['obs_id'],
									'obs_key' => 'Adherence'
								);
								$this->CI->observation_model->update_observation($observation_paramaters, $this->int_blog_id);
							}
							// send actual alert
							$log_string .= $this->send_obs_alert($observation, $message_id, $send_email, $extra_vars, false, $this->int_blog_id);
						} else {
							$log_string .= "Patient[{$user_id}][{$msg_id}][GOOD] did not miss for past 3 days asked, checked obs[{$item_obs_ids}]" . PHP_EOL;
						}
					}
				}
			}
		}
		$this->time_end = microtime(true);
		$log_string .= PHP_EOL . PHP_EOL . "process_alert_3_day_missed_items() Excecution Time: " . ($this->time_end - $this->time_start) ." (seconds)" . PHP_EOL;
		return $log_string;
	}




	/**
	 * Summary: run_process_obs_alerts - search for new observations and process them, checking for alerts
	 *
	 * @param $obs_id
	 * @return string
	 */
	public function process_obs_alerts($obs_id) {
		$this->time_start = microtime(true);
		// set blog id
		//$this->int_blog_id = $int_blog_id;

		// start logging
		$log_string = PHP_EOL . "---process_obs_alerts({$obs_id}) START" . PHP_EOL;

		// first get any new [alert_key or obs_id] observations that haven't been processed
		//$observations = $this->CI->observation_model->get_dm_observations($observation->obs_key, $obs_id, $this->int_blog_id);
		$observation = Observation::find($obs_id);
		if(!$observation) {
			return false;
		}

		// skip others (hack, bad planning)
		if( $observation->obs_key == 'Other' && (!in_array($observation['obs_value'], array('y', 'Y', 'n', 'N'))) ) {
			//continue 1;
		}
		$message_id = 'n/a';
		$extra_vars = array(); // extra_vars get stored for later email/sms/msg substitution
		$log_string .= "Checking ({$observation->obs_key}) observation[{$observation['obs_id']}][{$observation['obs_date']}][{$observation['obs_value']}]" . PHP_EOL;
		// get user data for observation
		$user = WpUser::find($observation->user_id);

		$userUcpData = $user->getUCP();
		/*
		//dd($userUcpData['ucp']->first());
		dd($userUcpData->where('items_id', '=', '27')->first());
		dd($userUcpData['ucp']->where('ucp_id', '>', '100')->first());
		*/
		//dd($userUcpData);
		$first_name = $user->meta()->where('meta_key', '=', 'last_names')->first();
		$last_name = $user->meta()->where('meta_key', '=', 'last_name')->first();
		$extra_vars['patientname'] = $first_name . ' ' . $last_name;
		//$extra_vars['alerts_url'] = ''.$this->get_alerts_url($observation['user_id'], $this->int_blog_id).'';
		$extra_vars['alerts_url'] = '';
		$extra_vars['alert_key'] = str_replace("_", " ", $observation->obs_key);
		//$user_data_ucp = $user_data[$observation['user_id']]['usermeta']['user_care_plan'];
		$obs_value = $observation['obs_value'];
		$result = false;
		$send_alert = false; // this will become a message string if an alert is found
		$send_email = false;
		// if users ucp has a value for the given alert key, compare and check if alert should be triggered
		switch ($observation->obs_key) {
			// Blood Pressure
			case 'Blood_Pressure':
				$result = $this->process_alert_obs_blood_pressure($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Blood Sugar
			case 'Blood_Sugar':
				$result = $this->process_alert_obs_blood_sugar($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Weight
			case 'Weight':
				$result = $this->process_alert_obs_weight($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Weekly cigarettes smoked
			case 'Cigarettes':
				$result = $this->process_alert_obs_cigarettes($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Call requested
			case 'Call':
				$result = $this->process_alert_obs_call($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Symptoms
			case 'Severity':
				$result = $this->process_alert_obs_severity($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Adherence
			case 'Adherence':
				$result = $this->process_alert_obs_adherence($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// Other
			case 'Other':
				$result = $this->process_alert_obs_other($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;

			// HSP
			case 'HSP_ER':
			case 'HSP_HOSP':
				$result = $this->process_alert_obs_hsp($user, $userUcpData, $observation, $this->int_blog_id);
				if($result) {
					$log_string .= $result['log_string'];
					$message_id = $result['message_id'];
					$send_alert = $result['send_alert'];
					$send_email = $result['send_email'];
					$extra_vars = array_merge($extra_vars, $result['extra_vars']);
				}
				break;
		}

		if(empty($result['label'])) {
			$log_string .= "{$observation->id} no label set, not adding any obsmeta " . PHP_EOL;
		} else {
			// insert observationmeta for dm result
			$observationMeta = new ObservationMeta();
			$observationMeta->obs_id = $observation->id;
			$observationMeta->comment_id = $observation->comment_id;
			$observationMeta->message_id = $message_id;
			$observationMeta->meta_key = 'dm_alert_level';
			$observationMeta->meta_value = $result['label'];
			$observationMeta->save();
			$log_string .= "added new observationmeta dm_alert_level - obsmeta_id = {$observation->id}" . PHP_EOL;

			$observationMeta = new ObservationMeta();
			$observationMeta->obs_id = $observation->id;
			$observationMeta->comment_id = $observation->comment_id;
			$observationMeta->message_id = $message_id;
			$observationMeta->meta_key = 'dm_log';
			$observationMeta->meta_value =$result['log_string'] . $result['send_alert'];
			$observationMeta->save();
			$log_string .= "added new observationmeta dm_log - obsmeta_id = {$observation->id}" . PHP_EOL;

			if ($send_alert !== false) {
				$log_string .= "SEND ALERT [{$send_alert}]" . PHP_EOL;
				// if exception, trigger alert flow
				$log_string .= $this->send_obs_alert($observation, $message_id, $send_email, $extra_vars, $observation->obs_method, $this->int_blog_id);
			}
		}

		// end logging and return
		$log_string .= PHP_EOL . "---process_obs_alerts({$observation->obs_key}) END" . PHP_EOL;
		$this->time_end = microtime(true);
		$log_string .= PHP_EOL . "{$observation->obs_key} Excecution Time: " . ($this->time_end - $this->time_start) ." (seconds)" . PHP_EOL;
		return $log_string;
	}


	/******************************************
	 ******************************************
	 *
	 * START process_alert_obs METHOD GROUP
	 *
	 ******************************************
	 ******************************************/

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_blood_pressure($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value)) {
			return false;
		}
		$extra_vars['bpvalue'] = $obs_value;
		// extract systolic from systolic/diastolic formatted value
		$pieces = explode("/", $obs_value);
		if(sizeof($pieces) == 2) {
			$obs_value = $pieces[0];
		}
		if(!isset($userUcpData['alert_keys']['Blood_Pressure']) || !isset($userUcpData['alert_keys']['Blood_Pressure_Low'])) {
			$log_string .= 'Missing UCP data for bp and/or bp low';
			$label = 'success';
		} else {
			$max_systolic_bp = $userUcpData['alert_keys']['Blood_Pressure'];
			$min_systolic_bp = $userUcpData['alert_keys']['Blood_Pressure_Low'];
			$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] BP High: {$max_systolic_bp},  BP Low: {$min_systolic_bp} (systolic) - obs_value={$obs_value}" . PHP_EOL;
			// compare observation value (systolic/diastolic) to patient max/min blood pressure limit
			if (!empty($obs_value) && !empty($min_systolic_bp) && !empty($max_systolic_bp)) {
				if ($obs_value <= $min_systolic_bp) { //81
					$message_id = 'CF_AL_02';
					$send_alert = "{$obs_value} (systolic) is <= {$min_systolic_bp} (systolic)";
					$send_email = true;
					$label = 'danger';
				} else if ($obs_value > $min_systolic_bp && $obs_value < 101) {
					$label = 'warning';
				} else if ($obs_value > 100 && $obs_value < 141) {
					$label = 'success';
				} else if ($obs_value > 140 && $obs_value <= $max_systolic_bp) {
					$label = 'warning';
				} else if ($obs_value > $max_systolic_bp) { //180
					$message_id = 'CF_AL_03';
					$send_alert = "{$obs_value} (systolic) is >= {$max_systolic_bp} (systolic)";
					$send_email = true;
					$label = 'danger';
				}
			} else {
				$log_string .= 'Missing UCP data for bp and/or bp low';
				$label = 'success';
			}
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_blood_sugar($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value)) {
			return false;
		}
		if(!isset($userUcpData['alert_keys']['Blood_Sugar']) || !isset($userUcpData['alert_keys']['Blood_Sugar_Low'])) {
			$log_string .= 'Missing UCP data for bs and/or bs low';
			$label = 'success';
		} else {
			$max_blood_sugar = $userUcpData['alert_keys']['Blood_Sugar'];
			$min_blood_sugar = $userUcpData['alert_keys']['Blood_Sugar_Low'];
			$extra_vars['bsvalue'] = $obs_value;
			$log_string = PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] BS High: {$max_blood_sugar}, BS Low: {$min_blood_sugar}" . PHP_EOL;
			if (!empty($obs_value) && !empty($min_blood_sugar) && !empty($max_blood_sugar)) {
				if ($obs_value <= $min_blood_sugar) { //61
					$message_id = 'CF_AL_04';
					$send_alert = "{$obs_value} (systolic) is <= {$min_blood_sugar} (systolic)";
					$send_email = true;
					$label = 'danger';
				} else if ($obs_value > $min_blood_sugar && $obs_value < 81) { //60
					$label = 'warning';
				} else if ($obs_value > 80 && $obs_value < 141) {
					$label = 'success';
				} else if ($obs_value > 140 && $obs_value <= $max_blood_sugar) { //351
					$label = 'warning';
				} else if ($obs_value > $max_blood_sugar) { //350
					$message_id = 'CF_AL_05';
					$send_alert = "{$obs_value} (systolic) is >= {$max_blood_sugar} (systolic)";
					$send_email = true;
					$label = 'danger';
				}
			} else {
				$log_string .= 'Missing UCP data for bs and/or bs low';
				$label = 'success';
			}
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_weight($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value)) {
			return false;
		}
		// WEIGHT PREVIOUS MATCH COMPARISON ALERT
		if(isset($userUcpData['obs_keys']['Weight_CHF']) && $userUcpData['obs_keys']['Weight_CHF'] == 'CHECKED') {
			// get previous weight observation
			$prev_obs = $user->observations()
				->whereRaw("obs_date < DATE_FORMAT('{$observation['obs_date']}', '%Y-%m-%d')")
				->where('id', '<', $observation['id'])
				->where('obs_key', '=', $observation['obs_key'])
				->where('obs_unit', '!=', 'invalid')
				->where('obs_unit', '!=', 'scheduled')
				->orderBy('obs_date', 'desc')
				->first();
			if (!empty($prev_obs)) {
				// calculate dates
				$dateLast 	= new DateTime($prev_obs->obs_date);
				$dateNow 	= new DateTime($observation['obs_date']);
				$intDiff    = date_diff($dateLast, $dateNow);
				$intWtDiff	= $obs_value - $prev_obs->obs_value;

				$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}({$observation['obs_date']})] Weight: {$obs_value}lbs CHF CHECK:: PREV OBSERVATION[{$prev_obs->obs_id}({$prev_obs->obs_date})][{$intDiff->days} days prior] Prev weight:{$prev_obs->obs_value}lbs" . PHP_EOL;

				switch ($intDiff->format('%a')) {
					case 0:
					case 1:
						if($intWtDiff > 2) {
							$send_alert = "{$obs_value}lbs is > 2lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
							$message_id = 'CF_AL_06';
							$send_email = true;
							$extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
							$extra_vars['chfperiod'] = '1 days';
							$label = 'danger';
						} else {
							$label = 'success';
							$log_string .= PHP_EOL . "Weight is good, {$prev_obs->obs_value} is < 2 lbs difference" . PHP_EOL;
						}
						break;

					case 2:
						if($intWtDiff > 4) {
							$send_alert = "{$obs_value}lbs is > 4lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
							$message_id = 'CF_AL_06';
							$send_email = true;
							$extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
							$extra_vars['chfperiod'] = '2 days';
							$label = 'danger';
						} else {
							$label = 'success';
							$log_string .= PHP_EOL . "Weight is good, {$prev_obs->obs_value} is < 4 lbs difference" . PHP_EOL;
						}
						break;

					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
						if($intWtDiff > 5) {
							$send_alert = "{$obs_value}lbs is > 4lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
							$message_id = 'CF_AL_06';
							$send_email = true;
							$extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
							$extra_vars['chfperiod'] = "{$intDiff->days} days";
							$label = 'danger';
						} else {
							$label = 'success';
							$log_string .= PHP_EOL . "Weight is good, {$prev_obs->obs_value} is < 5 lbs difference" . PHP_EOL;
						}
						break;
						break;

					default:
						$send_alert = "Patient hasnt reported weight in {$intDiff->days} days";
						$message_id = 'CF_AL_22';
						$send_email = true;
						$extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
						$extra_vars['chfperiod'] = "{$intDiff->days} days";
						$label = 'danger';
						break;
				}
			} else {
				$label = 'success';
				$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] Missing required previous obs for chf check" . PHP_EOL;
			}
		} else {
			$label = 'success';
			$log_string .= PHP_EOL . "user does not have chf checked for monitoring, checking weight" . PHP_EOL;
			// WEIGHT TARGET ALERT
			$max_cigs = 4;
			if(!isset($userUcpData['alert_keys']['Weight'])) {
				$log_string .= PHP_EOL . "user does not have a target weight set, cannot check" . PHP_EOL;
				$label = 'success';
			} else {
				$max_weight = $userUcpData['alert_keys']['Weight'];
				$obs_value = $obs_value;
				if (($obs_value !== false)) {
					$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] User {$observation['user_id']} Weight: {$obs_value}" . PHP_EOL;
					$label = 'success';
					if ($max_weight) {
						if (($obs_value / $max_weight) > 1.15) {
							$log_string .= PHP_EOL . " {$obs_value} / {$max_weight} > 1.15 = danger " . PHP_EOL;
							$label = 'danger';
						} else if (($obs_value / $max_weight) >= 1.06) {
							$log_string .= PHP_EOL . " {$obs_value} / {$max_weight} > 1.06 && <= 1.15 = warning " . PHP_EOL;
							$label = 'warning';
						} else if (($obs_value / $max_weight) > 1) {
							$log_string .= PHP_EOL . " {$obs_value} / {$max_weight} > 0 && <= 1.06 = success " . PHP_EOL;
							$label = 'success';
						} else {
							$log_string .= PHP_EOL . " Hit no range, should be success" . PHP_EOL;
							$label = 'success';
						}
					}
				}
			}
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_severity($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value)) {
			return false;
		}
		$max_severity = 7;
		if($obs_value < 4) {
			$label = 'success';
			$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] Severity: {$obs_value} < 4" . PHP_EOL;
		} else if($obs_value > 3 && $obs_value < 7) {
			$label = 'warning';
			$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] Severity: {$obs_value} > 3 && < 7" . PHP_EOL;
		} else if($obs_value > 6) {
			$label = 'danger';
		}
		// here is a hack to get symptoms text, first get parent observation (menu)
		/*
		$parent_observation_info = $this->CI->observation_model->get_parent_symptom_observation($observation['comment_id'], ((int)$observation['sequence_id']-1), $this->int_blog_id);
		if($parent_observation_info) {
			// get meta_key for parent menu, rpt_sum_resp_txt-{#}
			$itemmeta_result = $this->CI->rules_model->get_itemmeta_value_by_key($parent_observation_info->items_id, 'rpt_sum_resp_txt-' . (int)$parent_observation_info->obs_value, $this->int_blog_id);
			if(isset($itemmeta_result->meta_value)){
				$extra_vars['symptom'] = $itemmeta_result->meta_value;
			}
		}
		*/
		if(($obs_value !== false) && $obs_value >= $max_severity) {
			$log_string .= PHP_EOL . "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}] Severity: {$obs_value}" . PHP_EOL;
			$send_alert = "{$obs_value} is >= {$max_severity}";
			$send_email = true;
			$message_id = 'CF_AL_08';
			$extra_vars['symsev'] = $obs_value;
			$label = 'danger';
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_cigarettes($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value) && $obs_value != 0) {
			return false;
		}
		$max_cigs = 4;
		if(isset($userUcpData['alert_keys']['Cigarettes'])) {
			$max_cigs = $userUcpData['alert_keys']['Cigarettes'];
		}
		if($obs_value > $max_cigs) {
			$label = 'danger';
			$message_id = 'CF_AL_07';
			$send_alert = "Patient cigs too high, {$obs_value} > 4";
			$log_string = "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}][ucp cigs={$max_cigs}] cigs too high, {$obs_value} > {$max_cigs}" . PHP_EOL;
			$send_email = false;
		} else {
			$log_string = "OBSERVATION[{$observation['obs_id']}] Patient[{$observation['user_id']}][ucp cigs={$max_cigs}] cigs lower than ucp max, {$obs_value} > {$max_cigs}" . PHP_EOL;
			$label = 'success';
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}


	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_call($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = 'danger';
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		$message_id = 'CF_AL_01';
		$send_alert = "Patient requested a call" . PHP_EOL;
		$log_string = "OBSERVATION[{$observation['obs_id']}]  Patient[{$observation['user_id']}] requested a CALL" . PHP_EOL;
		$send_email = false;
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_adherence($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$obs_value = $observation['obs_value'];
		$log_string = "OBSERVATION[{$observation['obs_id']}]  Patient[{$observation['user_id']}] obs_value = " . $obs_value . PHP_EOL;

		// start
		if(strtoupper($obs_value) == 'Y') {
			$label = 'success';
		} else if(strtoupper($obs_value) == 'N') {
			$label = 'danger';
		} else {
			// this is where we can pick up missed meds, if the obs is NR and from previous day we can close it out here
			$obs_date = date_create($observation['obs_date']);
			if(($obs_date->format('Y-m-d')) < date("Y-m-d")) {
				// date is prior so we can close it out
				$log_string .= " Non Response from over 1 day ago" . PHP_EOL;
				$label = 'danger';
			}
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}

	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_other($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = false;
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$obs_value = $observation['obs_value'];
		if(empty($obs_value) || (strtoupper($obs_value) != 'Y' && strtoupper($obs_value) != 'N')) {
			return false;
		}
		$log_string = "OBSERVATION[{$observation['obs_id']}] obs_value = " . $obs_value . PHP_EOL;

		// start
		if(strtoupper($obs_value) == 'Y') {
			$label = 'success';
		} else if(strtoupper($obs_value) == 'N') {
			$label = 'danger';
		}
		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);
		return $result_array;
	}


	/**
	 * @param $userUcpData
	 * @param $observation
	 * @return array
	 */
	public function process_alert_obs_hsp($user, $userUcpData, $observation, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		// defaults
		$label = 'warning';
		$extra_vars = array();
		$message_id = '';
		$send_alert = false;
		$send_email = false;
		$log_string = '';

		// start
		$obs_value = $observation['obs_value'];
		if(empty($obs_value) && $obs_value != 0) {
			return false;
		}
		if(strtolower($obs_value) == 'b') {
			return false;
		}
		$obs_date = new DateTime($observation['obs_date']);
		//echo $obs_date->format('m/d');

		//$log_string = "OBSERVATION[{$observation['obs_id']}] obs_value = " . $obs_value . PHP_EOL;
		$log_string = "";
		if($observation->obs_key == 'HSP_ER') {
			if(strtolower($obs_value) == 'c') {
				$log_string .= "Patient in the ER as of ".$obs_date->format('m/d').", follow up required";
				$message_id = 'CF_AL_23'; // HSP_ER + C
				$send_alert = '';
			} else {
				$log_string .= "Visited ER on ".str_replace('_', '/', $obs_value).", follow up required";
				$message_id = 'CF_AL_24'; // HSP_ER + dd_mm
				$send_alert = '';
			}
		} else if($observation->obs_key == 'HSP_HOSP') {
			if(strtolower($obs_value) == 'c') {
				$log_string .= "Patient in the Hospital as of ".$obs_date->format('m/d').", follow up required";
				$message_id = 'CF_AL_25'; // HSP_HOSP + C
				$send_alert = '';
			} else {
				$log_string .= "Hospital Discharge on ".str_replace('_', '/', $obs_value).", follow up required";
				$message_id = 'CF_AL_26'; // HSP_HOSP + dd_mm
				$send_alert = '';
			}
		}

		$result_array = array(
			'log_string' => $log_string,
			'message_id' => $message_id,
			'send_alert' => $send_alert,
			'send_email' => $send_email,
			'extra_vars' => $extra_vars,
			'label' => $label
		);

		// insert activity
		$activity_params = array(
			'type' => 'HospitalVisit',
			'duration' => '0',
			'duration_unit' => 'minutes',
			'patient_id' => $observation['user_id'],
			'provider_id' => '0',
			'logger_id' => $observation['user_id'],
			'comment_id' => '0',
			'sequence_id' => '0',
			'obs_message_id' => '',
			'logged_from' => 'dm',
			'performed_at' => $observation['obs_date'],
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		);
		$this->CI->db->insert('lv_activities', $activity_params);

		//var_dump($result_array);die();
		return $result_array;
	}

	/******************************************
	 ******************************************
	 *
	 * END process_alert_obs METHOD GROUP
	 *
	 ******************************************
	 ******************************************/

	/**
	 * Summary: send_obs_alert gets passed an observation that was found to trigger an alert, and processes it
	 *
	 * @param $observation
	 * @param $message_id
	 * @return string
	 */
	public function send_obs_alert($observation, $message_id, $send_email, $extra_vars, $source = false, $int_blog_id) {
		// set blog id
		$this->int_blog_id = $int_blog_id;

		$log_string = PHP_EOL;

		// alert_sort_weight / alert_level
		$alert_sort_weight = 0;
		$alert_level = 'success';
		$msg_info = $this->get_alert_msg_info($message_id);
		if(!empty($msg_info)) {
			$alert_sort_weight = $msg_info['alert_sort_weight'];
			$alert_level = $msg_info['alert_level'];
		}

		$status = 'PA';
		if(!$send_email) {
			$status = 'NR';
			// override send email, set to NR if source = in-office visit
			if ($source) {
				if (strtolower($source) == 'ov_reading') {
					$status = 'RV';
					$log_string .= "source = ov_reading, status = RV" . PHP_EOL;
				}
			}
		} else {
			// override send email, set to NR if source = in-office visit
			if ($source) {
				if (strtolower($source) == 'ov_reading') {
					$send_email = false;
					$status = 'RV';
					$log_string .= "source = ov_reading, dont send email, status = RV" . PHP_EOL;
				}
			}
			// override send email, set to NR if > 1day old
			if (isset($observation['obs_date'])) {
				if (strtotime($observation['obs_date']) < strtotime('-1 days')) {
					$send_email = false;
					$status = 'NR';
					$log_string .= "obs_date > 1 day old, dont send email, status = NR" . PHP_EOL;
				}
			}
		}


		$serial_content = serialize(array(
			'status' => $status,
			'alert_level' => $alert_level,
			'obsid' => $observation['obs_id'],
			'comment_id' => $observation['obs_comment_id'],
			'message_id' => $message_id,
			'user' => $observation['user_id'],
			'modifier' => 'dmon',
			'blogid' => $this->int_blog_id,
			'date_time' => date("Y-m-d H:i:s")
		));

		// insert observationmeta
		$observationMeta = new ObservationMeta();
		$observationMeta->obs_id = $observation->id;
		$observationMeta->comment_id = $observation->comment_id;
		$observationMeta->message_id = $message_id;
		$observationMeta->meta_key = 'alert_status_hist';
		$observationMeta->meta_value = $serial_content;
		$observationMeta->save();
		$log_string .= "added new observationmeta alert_status_hist - obsmeta_id = {$observationMeta->id}" . PHP_EOL;

		$observationMeta = new ObservationMeta();
		$observationMeta->obs_id = $observation->id;
		$observationMeta->comment_id = $observation->comment_id;
		$observationMeta->message_id = $message_id;
		$observationMeta->meta_key = 'alert_status_change';
		$observationMeta->meta_value = $serial_content;
		$observationMeta->save();
		$log_string .= "added new observationmeta alert_status_change - obsmeta_id = {$observationMeta->id}" . PHP_EOL;

		$observationMeta = new ObservationMeta();
		$observationMeta->obs_id = $observation->id;
		$observationMeta->comment_id = $observation->comment_id;
		$observationMeta->message_id = $message_id;
		$observationMeta->meta_key = 'alert_sort_weight';
		$observationMeta->meta_value = $alert_sort_weight;
		$observationMeta->save();
		$log_string .= "added new observationmeta alert_sort_weight - obsmeta_id = {$observationMeta->id}" . PHP_EOL;

		// send email
		if($send_email) {
			$log_string .= $this->send_email($observation, $message_id, $extra_vars, $this->int_blog_id);
		} else {
			$log_string .= 'No email sent' . PHP_EOL;
		}
		return $log_string;
	}

	/**
	 * @param $email_message
	 * @param $extra_vars
	 * @return mixed
	 */
	function process_message_substitutions($email_message, $extra_vars) {
		if(!empty($extra_vars)) {
			foreach ($extra_vars as $substitute => $value) {
				if (strpos($email_message, '#' . $substitute . '#') !== false) {
					$email_message = str_replace('#' . $substitute . '#', $value, $email_message);
				}
			}
		}
		return $email_message;
	}

	/**
	 * @param $user_id
	 * @param $message_id
	 * @param $extra_vars
	 * @param int $int_blog_id
	 * @return bool|string
	 */
	public function send_email($observation, $message_id, $extra_vars, $int_blog_id = 7) {

		// get user info
		$user = WpUser::find($observation['user_id']);
		$user_meta_config = WpUserMeta::where('user_id',$user->ID)->where('meta_key','like','%config%')->first();
		$user_meta_blog = WpUserMeta::where('user_id',$user->ID)->where('meta_key','primary_blog')->first();
		$user_data = unserialize($user_meta_config->meta_value);
		// get recipients
		if(!array_key_exists('send_alert_to',$user_data)) {
			return false;
		}

		// get message info
		$msgCPRules = new MsgCPRules();
		$message_info = $msgCPRules->getQuestion($message_id, $user->ID, 'EMAIL_'.$user_data['preferred_contact_language'], $user_meta_blog->meta_value, 'SOL');

		//Breaks down here, suspect the params are not as expected in getQuestion()

		if(empty($message_info)){
			return false;
		}

		// set email params
		$email_subject = 'New Alert from CircleLink Health CPM';
		$email_message = $message_info->message;
		$email_message = process_message_substitutions($email_message, $extra_vars);
		$data = array('message' => $email_message);


		$email_sent_list = array();
		foreach($user_data['send_alert_to'] as $recipient_id) {

			$provider_user = WpUser::find($recipient_id);
			$email = $provider_user->user_email;

			Mail::send('alert', $data, function($message) use ($email,$email_subject) {
				$message->from('Support@CircleLinkHealth.com', 'CircleLink Health');
				$message->to($email)->subject($email_subject);
			});
				$email_sent_list[] = $provider_user->user_email;
		}

		dd($email_sent_list);

		// log to db by adding comment record
		$comment_content = array(
			'user_id' => $user->ID,
			'subject' => $email_subject,
			'message' => $email_message,
			'recipients' => $email_sent_list,
		);
		$comment_params = array(
			'comment_content' => serialize($comment_content),
			'user_id' => $user->ID,
			'comment_type' => 'dm_alert_email',
			'comment_parent' => $observation['comment_id']
		);
		$comment_id = $this->CI->comments_model->insert_comment($comment_params, $this->int_blog_id);
	}


	/**
	 * @param $user_id
	 * @return string
	 */
	function get_alerts_url($user_id, $blog_id) {
		$domain = $this->CI->users_model->get_blog_domain($blog_id);
		$alerts_url = 'https://'. $domain . '/alerts/?user=' . $user_id;
		$alerts_url = $this->CI->tinyurl->shorten($alerts_url);
		return $alerts_url;
	}


	public function get_alert_msg_info($alert_msg_id) {
		if($alert_msg_id == 'CF_AL_01') {
			$alert_sort_weight = 7;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_02') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_03') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_04') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_05') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_06') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_07') {
			$alert_sort_weight = 7;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_08') {
			$alert_sort_weight = 9;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_09') {
			$alert_sort_weight = 3;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_10') {
			$alert_sort_weight = 3;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_11') {
			$alert_sort_weight = 3;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_12') {
			$alert_sort_weight = 3;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_13') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_14') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_15') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_16') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_17') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_18') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_19') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_20') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_21') {
			$alert_sort_weight = 5;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_22') {
			$alert_sort_weight = 5;
			$alert_level = 'danger';
		} else if($alert_msg_id == 'CF_AL_23') { // HSP_ER + C
			$alert_sort_weight = 7;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_24') { // HSP_ER + mm/dd
			$alert_sort_weight = 7;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_25') { // HSP_HOSP + c
			$alert_sort_weight = 7;
			$alert_level = 'warning';
		} else if($alert_msg_id == 'CF_AL_26') { // HSP_HOSP + mm/dd
			$alert_sort_weight = 7;
			$alert_level = 'warning';
		}
		return array('alert_sort_weight' => $alert_sort_weight, 'alert_level' => $alert_level);
	}

}
