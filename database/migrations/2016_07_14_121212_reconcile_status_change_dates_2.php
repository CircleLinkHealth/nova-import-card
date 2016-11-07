<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

class ReconcileStatusChangeDates2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$usersPausedNoDate = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('patientInfo')->whereHas('patientInfo', function ($pq) {
			$pq->where(function ($query) {
				$query->where('ccm_status', '=', 'paused');
			});
			$pq->where(function ($query) {
				$query->where('date_paused', '=', '')->orWhereNull('date_paused');
			});
		})->get();

		/*
		foreach($usersPausedNoDate as $user) {
			echo PHP_EOL.$user->id;
			echo PHP_EOL.$user->patientInfo->ccm_status;
			echo PHP_EOL.$user->patientInfo->date_paused;
		}
		*/

		$usersWithdrawnNoDate = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('patientInfo')->whereHas('patientInfo', function ($pq) {
			$pq->where(function ($query) {
				$query->where('ccm_status', '=', 'withdrawn');
			});
			$pq->where(function ($query) {
				$query->where('date_withdrawn', '=', '')->orWhereNull('date_withdrawn');
			});
		})->get();

		echo 'Process usersPausedNoDate - Users found: '.$usersPausedNoDate->count().PHP_EOL;
		$i = 0;
		foreach($usersPausedNoDate as $user) {
			$this->processDateOfLastNote($user, 'paused');
		}

		echo 'Process usersWithdrawnNoDate - Users found: '.$usersWithdrawnNoDate->count().PHP_EOL;
		foreach($usersWithdrawnNoDate as $user) {
			$this->processDateOfLastNote($user, 'withdrawn');
		}
	}

	function processDateOfLastNote($user, $status) {
		echo '---------START-------------'.PHP_EOL;
        echo 'Processing user ' . $user->id . PHP_EOL;

		$activity1comment = '';
		$activity1status = '';
		$activity1date = '';
		$activity2comment = '';
		$activity2status = '';
		$activity2date = '';
		$activity3comment = '';
		$activity3status = '';
		$activity3date = '';
		$activities = $user->notes()
			->orderBy('performed_at', 'DESC')
			->limit(3)
			->get();
		if($activities->count() > 0) {
			$a = 0;
			foreach($activities as $activity) {
				$comment = $activity->body;
				$callStatus = '';
				$call = $activity->call()->first();
				if($call) {
					$callStatus = $call->status;
				}
				if($a == 0) {
					$activity1comment = $activity->id . ' ' . $comment;
					$activity1status = $callStatus;
					$activity1date = $activity->performed_at;
				}
				if($a == 1) {
					$activity2comment = $activity->id . ' ' . $comment;
					$activity2status = $callStatus;
					$activity2date = $activity->performed_at;
				}
				if($a == 2) {
					$activity3comment = $activity->id . ' ' . $comment;
					$activity3status = $callStatus;
					$activity3date = $activity->performed_at;
				}
				$a++;
			}
		}
		if($activity1date > '2016-06-01') {
			echo 'Activity: ' . $activity1date . PHP_EOL;
			echo 'Comment: ' . $activity1comment . PHP_EOL;
			echo 'Activity 2: ' . $activity2date . PHP_EOL;
			echo 'Comment 2: ' . $activity2comment . PHP_EOL;
			if($status == 'paused') {
				$user->patientInfo->date_paused = $activity1date;
			} else {
				$user->patientInfo->date_withdrawn = $activity1date;
			}
			$user->patientInfo->save();
			echo 'POPULATED date_'.$status.' with '.$activity1date . PHP_EOL;
		} else {
			echo 'Note date too old, ' . $activity1date . PHP_EOL;
		}
		echo '---------END-------------'.PHP_EOL;
		echo PHP_EOL.PHP_EOL.PHP_EOL;
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
