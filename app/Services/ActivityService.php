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

	public function sendNoteToCareTeam($careteam, $url, $performed_at){

		Mail::send('emails.welcome', ['key' => 'value'], function($message)
		{
			$message->to('foo@example.com', 'John Smith')->subject('Welcome!');
		});
		return true;

	}

}
