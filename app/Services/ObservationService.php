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

		// process message id
		$pieces = explode('/', $obsMessageId);
		if (count($pieces) == 1) {
			// normal message, straight /messageId
			$obsMessageId = $obsMessageId;
		} else if (count($pieces) == 2) {
			// semi-normal message, qstype/messageId
			$qstype = $pieces[0];
			$obsMessageId = $pieces[1];
		}

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
			$qsType = $msgCPRules->getQsType($obsMessageId, $userId);
			$currQuestionInfo = $msgCPRules->getQuestion($obsMessageId, $userId, 'SMS_EN', $wpUser->blogId(), $qsType);
			$obsKey = $currQuestionInfo->obs_key;
		}

		// find comment
		// @todo bring comment update back to this service
		$commentId = 0;
		$comment = Comment::find($parentId);
		if ($comment) {
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
		$newObservation->obs_key = $obsKey;
		//arrays to validate input
		$from = array("_","y");
		$to = array("/","Y");
		$newObservation->obs_value = str_replace($from, $to, $obsValue);
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

		return true;

		if(!empty($commentId)) {
			//Next Message Block
			$msgChooser = new MsgChooser();
			$msgChooser->setAppAnswerAndNextMessage($userId, $commentId, $obsMessageId, $obsValue, false);
			return true;
		} else {
			return false;
		}

	}

}
