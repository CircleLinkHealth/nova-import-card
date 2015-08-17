<?php namespace App\Services;

use App\Activity;
use App\Comment;
use App\Observation;
use App\WpUser;
use App\WpUserMeta;
use Carbon\Carbon;

class ObservationService {

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey) {
		// get user
		$wpUser = WpUser::find($userId);

		// update comment
		$comment = Comment::find($parentId);
		$comment_array = unserialize($comment['comment_content']);
		$comment_array[][$obsKey] = $obsValue;
		$comment->comment_content = serialize($comment_array);
		$comment->comment_type = 'state_app';
		$commentBlogTable = 'wp_'.$wpUser->blogId().'_comments';
		$comment->setTable($commentBlogTable);
		$savedComm = $comment->save();

		$newObservation = new Observation();
		$newObservation->comment_id = $comment->comment_ID;
		$newObservation->user_id = $userId;
		//Needs discussion
		$newObservation->obs_date = $obsDate;
		$newObservation->obs_date_gmt = Carbon::createFromFormat('Y-m-d H:i:s', $newObservation->obs_date)->setTimezone('GMT');
		$newObservation->sequence_id = 0;
		$newObservation->obs_message_id = $obsMessageId;
		$newObservation->obs_method = $comment->comment_type;
		$newObservation->obs_key = $obsKey;
		$newObservation->obs_value = $obsValue;
		$newObservation->obs_unit = '';
		//$savedObs = $newObservation->save();

		//Get Blog id for current user
		$obsBlogTable = 'ma_'.$wpUser->blogId().'_observations';
		//Set tables names
		$newObservation->setTable($obsBlogTable);
		$savedObs = $newObservation->save();

		//Next Message Block
		$msgChooser = new MsgChooser();
		$msgChooser->setNextMessage($wpUser->blogId(), $comment->comment_ID, $newObservation->obs_message_id,  $newObservation->obs_value);

		return true;
	}

}
