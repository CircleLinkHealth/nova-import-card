<?php namespace App\Services;

use App\Comment;
use App\Observation;
use App\ObservationMeta;
use App\User;
use DateTime;
use DateTimeZone;

class ObservationService {

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey, $timezone, $source = 'manual_input', $isStartingObs = 'N') {

		// get user
		$wpUser = User::find($userId);

		// set sequence, usually 0
		$sequence = 0;

		// arrays to validate input
		$from = array("_","y","n");
		$to = array("/","Y","N");
		$obsValue = str_replace($from, $to, $obsValue);

		// first and foremost, check if $isStartingObs, and if so, update and return true if already exists
		if($isStartingObs == 'Y') {
			// get starting observation for user
			// if found update and return, if not proceed to add
			$observation = Observation::where('obs_message_id', '=', $obsMessageId)
				->whereHas('meta', function($q){
					$q->where('meta_key', '=', 'starting_observation');
				})
				->where('user_id', '=', $userId)
				->orderBy('id', 'asc')
				->first();
			if($observation) {
				if($observation->meta->count() > 0) {
					// update existing observation and return
					$observation->obs_value = $obsValue;
					$observation->save();
					return true;
				}
			}
		}

		// process obs_key
		// harccode change 3.0.6 - $wpUser->program_id is now '16'
		if (empty($obsKey)) {
			$msgCPRules = new MsgCPRules;
			$qsType = $msgCPRules->getQsType($obsMessageId, '16');
			$currQuestionInfo = $msgCPRules->getQuestion($obsMessageId, $userId, 'SMS_EN', '10', $qsType);
			$obsKey = $currQuestionInfo->obs_key;
		}

		// create new comment thread if one doesnt exist yet
		$commentId = $parentId;
		if(empty($parentId)) {
			$comment = new Comment;
			$comment->comment_post_ID = 0;
			$comment->comment_author = $obsMessageId;
			$comment->comment_author_email = '';
			$comment->comment_author_url = '';
			$comment->comment_content = 'observation message thread';
			$comment->comment_type = 'message_thread';
			$comment->comment_parent = 0;
			$comment->user_id = $userId;
			$comment->comment_author_IP = '127.0.0.1';
			$comment->comment_agent = 'N/A';
			$comment->comment_date = $obsDate;
			$comment->comment_date_gmt = $obsDate;
			$comment->comment_approved = 1;
			$comment->save();
			$commentId = $comment->id;
		}



		// SAMPLES, compare
		//$obsDate = '2015-10-29 07:52:13';
		//$my_timezone = new DateTimeZone("America/Denver");

		//$obsDate = '2015-10-29 09:52:13';
		//$my_timezone = new DateTimeZone("America/New_York");

		//$obsDate = '2015-10-29 19:22:13';
		//$my_timezone = new DateTimeZone("Asia/Kolkata");

		//$my_timezone = new DateTimeZone("America/New_York");
		//$my_timezone = new DateTimeZone("Asia/Kolkata");
		//$my_timezone = new DateTimeZone("America/New_York");
		//$my_timezone = new DateTimeZone("America/Denver");
		//$my_timezone = new DateTimeZone("America/Los_Angeles");
		$my_timezone = new DateTimeZone($timezone);
		$server_timezone = new DateTimeZone("UTC");

		$my_date = new DateTime($obsDate, $my_timezone);
		$server_date = new DateTime($obsDate, $server_timezone);

		$my_offset = $my_timezone->getOffset($my_date);
		$server_offset = $server_timezone->getOffset($server_date);

		$diff = $my_offset - $server_offset;

		/*
		echo var_dump(($diff / 3600))."<br>";
		echo $obsDate ."<br>";
		echo "--------------<br>";
		echo 'my_date = '.$my_date->format('Y-m-d H:i:s') ."<br>";
		echo 'my_date (u) = '.$my_date->format('U') ."<br>";
		echo 'my_date date w/ (u) = '.date('Y-m-d H:i:s', ($my_date->format('U'))) ."<br>";
		echo 'my_date u + diff = '.($my_date->format('U') + $diff) ."<br>";
		echo 'my_date date w/ (u+diff) = '.date('Y-m-d H:i:s', ($my_date->format('U') + $diff)) ."<br>";
		*/

		$tzGmtDateLocal = date('Y-m-d H:i:s', ($my_date->format('U') + $diff));
		$tzGmtDate = date('Y-m-d H:i:s', ($my_date->format('U')));
		//dd($tzGmtDate);

		// insert new observation
		$newObservation = new Observation;
		$newObservation->comment_id = $commentId;
		$newObservation->obs_date = $obsDate;
		$newObservation->obs_date_gmt = gmdate( $obsDate);
		$newObservation->sequence_id = $sequence;
		$newObservation->obs_message_id = $obsMessageId;
		$newObservation->obs_method = $source;
		$newObservation->user_id = $userId;
		$newObservation->obs_value = $obsValue;
		$newObservation->obs_key = $obsKey;
		$newObservation->obs_unit = '';
		$newObservation->save();

		// insert observationmeta for timezone
		$newObservationMeta = new ObservationMeta;
		$newObservationMeta->obs_id = $newObservation->id;
		$newObservationMeta->comment_id = $commentId;
		$newObservationMeta->message_id = $obsMessageId;
		$newObservationMeta->meta_key = 'timezone';
		$newObservationMeta->meta_value = $timezone;
		$newObservationMeta->save();

		// add meta for starting observation
		if($isStartingObs == 'Y') {
			$newObservationMeta = new ObservationMeta;
			$newObservationMeta->obs_id = $newObservation->id;
			$newObservationMeta->comment_id = $commentId;
			$newObservationMeta->message_id = $obsMessageId;
			$newObservationMeta->meta_key = 'starting_observation';
			$newObservationMeta->meta_value = 'yes';
			$newObservationMeta->save();
		}

        //Hack to validate a1c.
        //This code is gross
        if ($obsKey == 'A1c') {
            return true;
        }

		// Next Message Block
		$msgChooser = new MsgChooser();

		// skip responses for adherence
		if($obsKey == 'Adherence') {
			return true;
		}

		if(!empty($commentId)) {
			//$msgChooser->setAppAnswerAndNextMessage($userId, $commentId, $obsMessageId, $obsValue, false);
			$msgChooser->setObsResponse($userId, $commentId, $obsMessageId, $obsValue, $obsDate, $sequence, false);
			return true;
		} else {
			$msgChooser->setObsResponse($userId, $commentId, $obsMessageId, $obsValue, $obsDate, $sequence, false);
			return true;
		}

	}

}
