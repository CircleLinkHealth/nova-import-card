<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

class MigrateDatePausedFromMeta extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// add approval meta to patient info
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('patientInfo')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
			echo 'Add missing meta to User->PatientInfo'.PHP_EOL;

			// set values
			if(!empty($user->getUserMetaByKey('active_date'))) {
				$user->patientInfo->active_date = $user->getUserMetaByKey('active_date');
			}

			if(!empty($user->getUserMetaByKey('date_paused'))) {
				$user->patientInfo->date_paused = $user->getUserMetaByKey('date_paused');
			}

			if(!empty($user->getUserMetaByKey('date_withdrawn'))) {
				$user->patientInfo->date_withdrawn = $user->getUserMetaByKey('date_withdrawn');
			}

			$user->patientInfo->save();
		}



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
