<?php namespace App\Services;

use App\Activity;
use App\Comment;
use App\Observation;
use App\ObservationMeta;
use App\WpUser;
use App\WpUserMeta;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class ObservationService {

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey, $timezone, $source = 'manual_input', $isStartingObs = 'N') {

		// get user
		$wpUser = WpUser::find($userId);

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
		if (empty($obsKey)) {
			$msgCPRules = new MsgCPRules;
			$qsType = $msgCPRules->getQsType($obsMessageId, $wpUser->program_id);
			$currQuestionInfo = $msgCPRules->getQuestion($obsMessageId, $userId, 'SMS_EN', $wpUser->program_id, $qsType);
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

		// insert new observation
		$newObservation = new Observation;
		$newObservation->comment_id = $commentId;
		$newObservation->obs_date = $obsDate;
		$newObservation->obs_date_gmt = $obsDate;
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
