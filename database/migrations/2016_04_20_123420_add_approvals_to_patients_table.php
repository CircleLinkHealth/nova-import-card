<?php

use App\PatientInfo;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddApprovalsToPatientsTable extends Migration {

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
			if ( ! Schema::hasColumn('patient_info', 'careplan_qa_approver')) {
				$table->string('careplan_qa_approver')->after('hospital_reminder_areas');
				$table->string('careplan_qa_date')->after('careplan_qa_approver');
				$table->string('careplan_provider_approver')->after('careplan_qa_date');
				$table->string('careplan_provider_date')->after('careplan_provider_approver');
				$table->string('careplan_status')->after('careplan_provider_date');
			}
		});


		// add approval meta to patient info
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('meta', 'patientInfo')->get();
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
			$user->patientInfo->careplan_qa_approver = $user->getUserMetaByKey('careplan_qa_approver');
			$user->patientInfo->careplan_qa_date = $user->getUserMetaByKey('careplan_qa_date');
			$user->patientInfo->careplan_provider_approver = $user->getUserMetaByKey('careplan_provider_approver');
			$user->patientInfo->careplan_provider_date = $user->getUserMetaByKey('careplan_provider_date');
			$user->patientInfo->careplan_status = $user->getUserMetaByKey('careplan_status');
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

		Schema::table('patient_info', function(Blueprint $table)
		{
			if ( Schema::hasColumn('patient_info', 'careplan_qa_approver')) {
				$table->dropColumn('careplan_qa_approver');
				$table->dropColumn('careplan_qa_date');
				$table->dropColumn('careplan_provider_approver');
				$table->dropColumn('careplan_provider_date');
				$table->dropColumn('careplan_status');
			}
		});

	}

}
