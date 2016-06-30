<?php

use App\Models\CCD\Ccda;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
use App\User;
use App\CareItemUserValue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNameTypeCcdMedicationsRound2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // RERUN ITEM MIGRATION
        // add approval meta to patient info
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'participant');
        })->with('patientInfo')->orderBy('ID', 'Desc')->get();
        echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
        $i = 0;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            // first check for ccda items
            /*
            $ccda = Ccda::where('user_id', '=', $user->ID)->first();
            if(!empty($ccda)) {
                //echo 'User has ccda. SKIPPING '.$user->ID.PHP_EOL.PHP_EOL;
                //continue 1;
            }
            */

            // get care_item_user_values - medication-list-details = 461
            CcdMedication::where('patient_id', '=', $user->ID)->delete();
            //if(empty($userItems)) {
                $careItemUserValue = CareItemUserValue::where('user_id', '=', $user->ID)->where('care_item_id', '=', 461)->first();
                if (!empty($careItemUserValue)) {
                    $ccdMedication = New CcdMedication;
                    $ccdMedication->patient_id = $user->ID;
                    $ccdMedication->name = $careItemUserValue->value;
                    $ccdMedication->save();
                    echo 'added CcdMedication '.$ccdMedication->id.' - val=' . $careItemUserValue->value . PHP_EOL;
                }
            //}
            // get care_item_user_values - allergies-details = 70
            CcdAllergy::where('patient_id', '=', $user->ID)->delete();
            //if(empty($userItems)) {
                $careItemUserValue = CareItemUserValue::where('user_id', '=', $user->ID)->where('care_item_id', '=', 70)->first();
                if (!empty($careItemUserValue)) {
                    $ccdAllergy = New CcdAllergy;
                    $ccdAllergy->patient_id = $user->ID;
                    $ccdAllergy->allergen_name = $careItemUserValue->value;
                    $ccdAllergy->save();
                    echo 'added' . $careItemUserValue->value . PHP_EOL;
                    echo 'added CcdAllergy '.$ccdAllergy->id.' - val=' . $careItemUserValue->value . PHP_EOL;
                }
            //}

            // get care_item_user_values - other-conditions-details = 411
            CcdProblem::where('patient_id', '=', $user->ID)->delete();
            //if(empty($userItems)) {
                $careItemUserValue = CareItemUserValue::where('user_id', '=', $user->ID)->where('care_item_id', '=', 411)->first();
                if (!empty($careItemUserValue)) {
                    $ccdProblem = New CcdProblem;
                    $ccdProblem->patient_id = $user->ID;
                    $ccdProblem->name = $careItemUserValue->value;
                    $ccdProblem->save();
                    echo 'added CcdProblem '.$ccdProblem->id.' - val=' . $careItemUserValue->value . PHP_EOL;
                }
            //}
            $i++;

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
