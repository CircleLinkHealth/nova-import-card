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

	public function storeObservationFromApp($userId, $parentId, $obsValue, $obsDate, $obsMessageId, $obsKey, $timezone) {
		// get user
		$wpUser = WpUser::find($userId);

		// find comment
		$comment = DB::connection('mysql_no_prefix')
			->table('wp_' . $wpUser->blogId() . '_comments')
			->where('comment_ID', '=', $parentId)
			->first();

		// @todo bring comment update back to this service

		// insert new observation

		   $result =  DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observations')->insertGetId([
			'comment_id' => $comment->comment_ID,
			'user_id' => $userId,
			'obs_date' => $obsDate,
			'obs_date_gmt' => DateTime::createFromFormat('Y-m-d H:i:s', $obsDate),
			'sequence_id' => '0',
			'obs_message_id' => $obsMessageId,
			'obs_method' => $comment->comment_type,
			'obs_key' => $obsKey,
			'obs_value' => $obsValue,
			'obs_unit' => '',
		]);

		$query = DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observationmeta')->insertGetId([
			'obs_id' => $result,
			'comment_id' => $comment->comment_ID,
			'message_id' => $obsMessageId,
			'meta_key' => 'timezone',
			'meta_value' => $timezone
		]);

		//Next Message Block
		$msgChooser = new MsgChooser();
		$msgChooser->setAppAnswerAndNextMessage($userId, $comment->comment_ID, $obsMessageId,  $obsValue, false);
		if($query){return $result;}
		return false;

	}
}
