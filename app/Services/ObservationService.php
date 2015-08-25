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
			$result =  DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observations')->insertGetId([
				'comment_id' => '0',
				'user_id' => $userId,
				'obs_date' => $obsDate,
				'obs_date_gmt' => $obsDate,
				'sequence_id' => '0',
				'obs_message_id' => $symMenuMsgId,
				'obs_method' => 'manual_input',
				'obs_key' => 'Symptom',
				'obs_value' => $symMenuObsValue,
				'obs_unit' => '',
			]);
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
		$comment = DB::connection('mysql_no_prefix')
			->table('wp_' . $wpUser->blogId() . '_comments')
			->where('comment_ID', '=', $parentId)
			->first();

		// @todo bring comment update back to this service
		$commentId = 0;
		if($comment) {
			$commentId = $comment->comment_ID;
		} else {
			//dd("ObservationService->storeObservationFromApp() There is no state_app comment for today, looking for comment_id=$parentId... run scheduler");
		}

		// insert new observation
		$resultObsId =  DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observations')->insertGetId([
			'comment_id' => $commentId,
			'user_id' => $userId,
			'obs_date' => $obsDate,
			'obs_date_gmt' => $obsDate,
			'sequence_id' => $sequence,
			'obs_message_id' => $obsMessageId,
			'obs_method' => $source,
			'obs_key' => $obsKey,
			'obs_value' => $obsValue,
			'obs_unit' => '',
		]);

		$resultObsMetaId = DB::connection('mysql_no_prefix')->table('ma_'.$wpUser->blogId().'_observationmeta')->insertGetId([
			'obs_id' => $resultObsId,
			'comment_id' => $commentId,
			'message_id' => $obsMessageId,
			'meta_key' => 'timezone',
			'meta_value' => $timezone
		]);

		if($comment) {
			//Next Message Block
			$msgChooser = new MsgChooser();
			$msgChooser->setAppAnswerAndNextMessage($userId, $commentId, $obsMessageId, $obsValue, false);
		}
		if($resultObsMetaId){return $resultObsId;}
		return false;

	}




	public function insertObservation($blog_id, $intUserId, $qstype, $strMessageId, $strResponse, $readingDate = 'now', $source = 'smsoutbound') {
		$arg_list = func_get_args();
		dd($arg_list);
		$strResponse = urldecode($strResponse);
		//var_dump($arg_list);
		// validate the given value
		// $tmpArray = preg_split("/[ _]/", $strResponse);
		// $strResponse2 = $tmpArray[0];
		$strReturn = 'saved';
		$strValid = '';

		$readingDate = urldecode($readingDate);

		if ($qstype =='SYM') {
			$c=2;
			$strMessageId   = $arg_list[5];
			$strResponse    = $arg_list[6];
			$readingDate    = $arg_list[7];
			$source         = $arg_list[8];
		}

// echo "<br>QSType: $qstype  Response: $strResponse ";
// Response2: $strResponse2";

		$c=1;

		$ret =  $this->rules->getValidAnswer($blog_id, $qstype, $strMessageId, $strResponse);
		if(empty($ret)) {
			$tmp2  = $this->rules->getQuestion('CF_INV_10', $intUserId);
			$strReturn = $tmp2->message;
			$strValid = 'invalid';
		} else {
			$strResponse = $ret->value;
		}

		$strResponse = str_replace("_", "/", $strResponse);

		if ($qstype =='SYM') {
			$serialOutboundMessage = serialize(array($arg_list[3] => $arg_list[4], $arg_list[5] => $strResponse));
		} else {
			$serialOutboundMessage = serialize(array($strMessageId => $strResponse));
		}

// echo "<br>serialOutboundMessage: $serialOutboundMessage";
		// insert new comment record
		$lastkey = $this->mailman->writeOutboundSmsMessage($intUserId,$serialOutboundMessage,$strMessageId, 'manual_input',$blog_id, $readingDate);

// echo "<br>LastKey from writeOutboundSmsMessage: $lastkey";

		$ret = $this->rules->getQuestion($strMessageId, $intUserId, 'SMS_EN', $blog_id, $qstype);

		// manually set obs_key when a SYM is sent.
		if ($qstype =='SYM') {
			$c = 2;
			$symSymptoms = array('Symptom', 'Severity');
		} else {
			$symSymptoms = array($ret->obs_key);
		}

// echo '<br>Getting Question Information: ';
// print_r($arg_list);
// echo '<br>strResponse: '.$strResponse;
// echo '<br>C: '.$c;

		for ($i=0; $i < $c; $i++) {
			// if ($i > 0) {
			$mykey = 2*$i+3;
// echo '<br>MyKey: '.$mykey;
			$strMessageId = $arg_list[$mykey];
			$strResponse2 = ($i == ($c-1)) ? $strResponse : $arg_list[$mykey+1];
// echo '<br>$strResponse:'.$strResponse2;
			$strResponse2 = str_replace("_", "/", $strResponse2);
			$obs_key = $symSymptoms[$i];
			// }
			// echo '<br>MyKey: '.$mykey.' MsgID: '.$strMessageId.' Response: '.$strResponse;
			// .' User_id: '.$intUserId.' blog_id: '.$blog_id.' Question Type: '.$qstype.
			// .' Obs_Key: ' .$obs_key. " Seq: $i lastkey: $lastkey".'<br> ';
			$strReturnValue = $this->saveObs($i, $obs_key, $lastkey, $blog_id, $intUserId, $qstype, $strMessageId, $strResponse2, $readingDate, $source, $strValid);
		}
		echo $strReturn;
		return;
	}

	public function saveObs($seq, $obs_key, $lastkey, $blog_id, $intUserId, $qstype, $strMessageId, $strResponse, $readingDate = 'now', $source = 'smsoutbound', $strUnit = '') {

		$dateTime = new DateTime(urldecode($readingDate), new DateTimeZone('America/New_York'));
		$localTime = $dateTime->format('Y-m-d H:i:s');

		$dateTime->setTimezone(new DateTimeZone('UTC'));
		$gmtTime = $dateTime->format('Y-m-d H:i:s');

		$data = array(
			'comment_id' => $lastkey,
			'sequence_id' => $seq,
			'user_id' => $intUserId,
			'obs_date' => $localTime,
			'obs_date_gmt' => $gmtTime,
			'obs_message_id' => $strMessageId,
			'obs_method' => $qstype,
			'obs_key' => $obs_key,
			'obs_value' => $strResponse,
			'obs_unit' => $strUnit
		);

		// echo "<br>Data to be saved:";
		// print_r($data);
		$log_string = '';

		// insert new observation record
		$obs_id = $this->obs->insert_observation($data, $source, $blog_id);
		$log_string .= "added new observation - obs_id = {$obs_id}" . PHP_EOL;

		// insert observationmeta
		$serial_content = serialize(array(
			'status' => 'PA',
			'obsid' => $obs_id,
			'comment_id' => $lastkey,
			'message_id' => $strMessageId,
			'user' => $intUserId,
			'modifier' => 'manual',
			'blogid' => $blog_id,
			'date_time' => $localTime
		));

		$observationmeta_paramaters = array(
			'obs_id' => $obs_id,
			'comment_id' => $lastkey,
			'message_id' => $strMessageId,
			'meta_key' => 'source',
			'meta_value' => $source
		);
		$observationmeta_id = $this->obsmeta->insert_observationmeta($observationmeta_paramaters, $blog_id);
		$log_string .= "added new observationmeta source[{$source}] - obsmeta_id = {$observationmeta_id}" . PHP_EOL;

		error_log($log_string);
		return 'true';
	}
}
