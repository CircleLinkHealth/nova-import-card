<?php namespace App\Services;

use App\Activity;
use App\Comment;
use App\Observation;
use App\WpUser;
use App\WpUserMeta;
use DB;
use Carbon\Carbon;

class ObservationService {

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey) {
		// get user
		$wpUser = WpUser::find($userId);

		// find comment
		$comment = DB::connection('mysql_no_prefix')
			->table('wp_' . $wpUser->blogId() . '_comments')
			->where('comment_ID', '=', $parentId)
			->first();

		/*
		// update comment
		$comment_array = unserialize($comment['comment_content']);
		// find message in comment
		foreach($comment_array as $key => $observations) {
			foreach($observations as $msgId => $answer) {
				if($msgId == $obsMessageId) {
					$comment_array[$key][$msgId] = $obsValue;
				}
			}
		}
		//dd($comment_array);
		$comment->comment_content = serialize($comment_array);
		$commentBlogTable = 'wp_'.$wpUser->blogId().'_comments';
		$comment->setTable($commentBlogTable);
		$savedComm = $comment->save();
		*/

		// insert new observation
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
		//dd($newObservation->obs_value);
		$msgChooser->setAppAnswerAndNextMessage($userId, $comment->comment_ID, $newObservation->obs_message_id,  $newObservation->obs_value, false);

		return true;
	}

}
