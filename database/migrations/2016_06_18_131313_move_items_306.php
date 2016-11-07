<?php

use Illuminate\Database\Migrations\Migration;

class MoveItems306 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/*
		// add approval meta to patient info
		$users = User::whereHas('roles', function ($q) {
			$q->where('name', '=', 'participant');
		})->with('patientInfo')->get();
		echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
		$i = 0;
		foreach($users as $user) {
			echo 'Processing user '.$user->id.PHP_EOL;
			// first check for ccda items
			$ccda = Ccda::where('user_id', '=', $user->id)->first();
			if(!empty($ccda)) {
				echo 'User has ccda. SKIPPING '.$user->id.PHP_EOL.PHP_EOL;
				continue 1;
			}

			// get care_item_user_values - medication-list-details = 461
			$careItemUserValue = CareItemUserValue::where('user_id', '=', $user->id)->where('care_item_id', '=', 461)->first();
			if(!empty($careItemUserValue)) {
				$ccdMedication = New CcdMedication;
				$ccdMedication->patient_id = $user->id;
				$ccdMedication->name = $careItemUserValue->value;
				$ccdMedication->save();
				echo 'added' . $careItemUserValue->value.PHP_EOL;
			}
			// get care_item_user_values - allergies-details = 70
			$careItemUserValue = CareItemUserValue::where('user_id', '=', $user->id)->where('care_item_id', '=', 70)->first();
			if(!empty($careItemUserValue)) {
				$ccdAllergy = New CcdAllergy;
				$ccdAllergy->patient_id = $user->id;
				$ccdAllergy->allergen_name = $careItemUserValue->value;
				$ccdAllergy->save();
				echo 'added' . $careItemUserValue->value.PHP_EOL;
			}

			// get care_item_user_values - other-conditions-details = 411
			$careItemUserValue = CareItemUserValue::where('user_id', '=', $user->id)->where('care_item_id', '=', 411)->first();
			if(!empty($careItemUserValue)) {
				$ccdProblem = New CcdProblem;
				$ccdProblem->patient_id = $user->id;
				$ccdProblem->name = $careItemUserValue->value;
				$ccdProblem->save();
				echo 'added' . $careItemUserValue->value.PHP_EOL;
			}
			$i++;

		}
		*/



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
