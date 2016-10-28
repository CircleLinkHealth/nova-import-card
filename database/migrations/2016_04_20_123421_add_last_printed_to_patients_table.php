<?php

use App\PatientInfo;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLastPrintedToPatientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		// first run this down, clean slate each run
		echo 'Running down()'.PHP_EOL;
		$this->down();

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
			echo 'Add to User->PatientInfo'.PHP_EOL;

			// skip if no existing patientInfo
			if(!$user->patientInfo) {
				echo 'creating new patientInfo'.PHP_EOL;
				$patientInfo = new PatientInfo;
                $patientInfo->user_id = $user->id;
				$user->patientInfo()->save($patientInfo);
				$user->load('patientInfo');
			}

			// set values
			if(!empty($user->getUserMetaByKey('careplan_last_printed'))) {
				$user->patientInfo->careplan_last_printed = $user->getUserMetaByKey('careplan_last_printed') . ' 12:00:00';
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

		Schema::table('patient_info', function(Blueprint $table)
		{
			if ( Schema::hasColumn('patient_info', 'careplan_last_printed')) {
				$table->dropColumn('careplan_last_printed');
			}
		});

	}

}
