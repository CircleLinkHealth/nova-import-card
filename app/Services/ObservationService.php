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

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey, $timezone, $source = 'manual_input') {
		// get user
		$wpUser = WpUser::find($userId);

		// set sequence, usually 0
		$sequence = 0;

		// process message id
		$pieces = explode('/', $obsMessageId);
		if(count($pieces) == 1) {
			// normal message, straight /messageId
			$obsMessageId = $obsMessageId;
		} else if(count($pieces) == 2) {
			// semi-normal message, qstype/messageId
			$qstype = $pieces[0];
			$obsMessageId = $pieces[1];
		} else if(count($pieces) == 4) {
			// SYM symptom
			$qstype = $pieces[0];
			$symMenuMsgId = $pieces[1];
			$symMenuObsValue = $pieces[2];
			$obsMessageId = $pieces[3];
			// insert parent symptom menu observation
			$newObservation = new Observation;
			$newObservation->comment_id = '0';
			$newObservation->obs_date = $obsDate;
			$newObservation->obs_date_gmt = $obsDate;
			$newObservation->sequence_id = '0';
			$newObservation->obs_message_id = $symMenuMsgId;
			$newObservation->obs_method = 'manual_input';
			$newObservation->user_id = $userId;
			$newObservation->obs_key = 'Symptom';
			$newObservation->obs_value = $symMenuObsValue;
			$newObservation->obs_unit = '';
			$newObservation->save();
			$sequence = 1;
		}

		// process obs_key
		if(empty($obsKey)) {
			$msgCPRules = new MsgCPRules;
			$qsType = $msgCPRules->getQsType($obsMessageId, $userId);
			$currQuestionInfo = $msgCPRules->getQuestion($obsMessageId, $userId, 'SMS_EN', $wpUser->blogId(), $qsType);
			$obsKey = $currQuestionInfo->obs_key;
		}

		// find comment
		$comment = Comment::find($parentId);

		// @todo bring comment update back to this service
		$commentId = 0;
		if($comment) {
			$commentId = $comment->comment_ID;
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
		$newObservation->obs_value = $obsValue;
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

		if($comment) {
			//Next Message Block
			$msgChooser = new MsgChooser();
			$msgChooser->setAppAnswerAndNextMessage($userId, $commentId, $obsMessageId, $obsValue, 			false);
		}
		return true;

	}

}
