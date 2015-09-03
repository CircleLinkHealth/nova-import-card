<?php namespace App\Services;

use App\Activity;
use App\WpUser;
use App\WpUserMeta;
use Illuminate\Support\Facades\Mail;

class ActivityService {

	public function getTotalActivityTimeForMonth($userId, $month = false) {
		// if no month, set to current month
		if(!$month) {
			$month =  date('m');
		}
		$totalDuration = Activity::where( \DB::raw('MONTH(created_at)'), '=', $month )->where( 'patient_id', '=', $userId )->sum('duration');
		return $totalDuration;
	}

	public function reprocessMonthlyActivityTime($userIds = false, $month = false) {
		// if no month, set to current month
		if(!$month) {
			$month =  date('m');
		}

		if($userIds) {
			// cast userIds to array if string
			if(!is_array($userIds)) {
				$userIds = array($userIds);
			}
			$wpUsers = wpUser::whereIn('id', $userIds)->orderBy('ID', 'desc')->get();
		} else {
			// get all users
			$wpUsers = wpUser::orderBy('ID', 'desc')->get();
		}

		if(!empty($wpUsers)) {
			// loop through each user
			foreach($wpUsers as $wpUser) {
				// get all activities for user for month
				$totalDuration = $this->getTotalActivityTimeForMonth($wpUser->ID, $month);

				// update user_meta with total
				$userMeta = WpUserMeta::where('user_id', '=', $wpUser->ID)
					->where('meta_key', '=', 'cur_month_activity_time')->first();
				if(!$userMeta) {
					// add in initial user meta: cur_month_activity_time
					$newUserMetaAttr = array(
						'user_id' => $wpUser->ID,
						'meta_key' => 'cur_month_activity_time',
						'meta_value' => $totalDuration,
					);
					$newUserMeta = WpUserMeta::create($newUserMetaAttr);
					//echo "<pre>CREATED";var_dump($newUserMeta);echo "</pre>";die();
				} else {
					// update existing user meta: cur_month_activity_time
					$userMeta = WpUserMeta::where('user_id', '=', $wpUser->ID)
						->where('meta_key', '=', 'cur_month_activity_time')
						->update(array('meta_value' => $totalDuration));
					//echo "<pre>UPDATED";var_dump($totalDuration);echo "</pre>";die();
				}
			}
		}
		return true;
	}

	/**
	 * @param $careteam
	 * @param $url
	 * @param $performed_at
	 * @param $user_id
     */
	public function sendNoteToCareTeam($careteam, $url, $performed_at,$user_id){

		$user = WpUser::find($user_id);

		$careteam_emails = array();

		for($i = 0; $i < count($careteam); $i++){
			$provider_user = WpUser::find($careteam[$i]);
			$careteam_emails[] = $provider_user->user_email;
		}

		for($j = 0; $j < count($careteam_emails); $j++) {
				$email = $careteam_emails[$j];

			Mail::send('emails.welcome', ['key' => 'value'], function($message)
			{
				$message->to($message->user)->subject('New Note from '.$message->patient);
			});

		} return true;
	}


}
