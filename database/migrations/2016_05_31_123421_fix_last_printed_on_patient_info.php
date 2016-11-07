<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixLastPrintedOnPatientInfo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		echo 'Schema::patient_info approval columns'.PHP_EOL;
		Schema::table('patient_info', function(Blueprint $table)
		{
			if ( ! Schema::hasColumn('patient_info', 'careplan_last_printed')) {
				$table->string('careplan_last_printed')->after('hospital_reminder_areas');
			}
		});

		// add approval meta to patient info
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('patientInfo')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;

			// set values
			$value = $user->getUserMetaByKey('careplan_last_printed');
			if(!empty($value)) {
				echo 'Add to User->PatientInfo value='.$value.PHP_EOL;
				if(strlen($value) < 12) {
					$user->patientInfo->careplan_last_printed = $value . ' 12:00:00';
				} else {
					$user->patientInfo->careplan_last_printed = $value . '';
				}
				$user->patientInfo->save();
			}
		}


	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// no down
	}

}
