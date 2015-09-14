<?php namespace App\Services;

use App\Activity;
use App\WpUser;
use App\WpUserMeta;
use Carbon\Carbon;
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
	 * @param $logger_name
	 * @param $newNoteFlag (checks whether it's a new note or an old one)
	 * @return bool
	 */
	public function sendNoteToCareTeam(&$careteam, $url, $performed_at, $user_id, $logger_name, $newNoteFlag){

		/*
		 *  New note: "Please see new note for patient [patient name]: [link]"
		 *  Old/Fw'd note: "Please see forwarded note for patient [patient  name], created on [creation date] by [note creator]: [link]
		 */

		$user = WpUser::find($user_id);
		for($i = 0; $i < count($careteam); $i++){
			$provider_user = WpUser::find($careteam[$i]);
			$email = $provider_user->user_email;
			//$performed_at = Carbon::parse($performed_at)->diffForHumans();
			$data = array(
				'patient_name' => $user->display_name,
				'url' => $url,
				'time' => $performed_at,
				'logger' => $logger_name
			);

			if($newNoteFlag) {
				$email_view = 'emails.newnote';
			}	else {
				$email_view = 'emails.existingnote';
			}
			Mail::send($email_view, $data, function($message) use ($email) {
				$message->from('no-reply@careplanmanager.com', 'CircleLink Health');
				$message->to($email)->subject('You have received a new note notification from CarePlan Manager');
			});
		}
		return false;
//		dd(count(Mail::failures()));
//		if( count(Mail::failures()) > 0 ) {
//			return false;
//		} else {
//			return true;
//		}
	}
}
