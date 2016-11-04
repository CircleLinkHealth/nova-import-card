<?php

use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\CpmMisc;
use App\Services\CPM\CpmMiscService;
use App\User;
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


        DB::table('ccd_problems')->truncate();
        DB::table('ccd_medications')->truncate();
        DB::table('ccd_allergies')->truncate();

        // RERUN ITEM MIGRATION
        // add approval meta to patient info
        $users = User::orderBy('id', 'Desc')->get();
        echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
        $i = 0;
        foreach($users as $user) {
            echo 'Processing user ' . $user->id . PHP_EOL;
            $result = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::ALLERGIES);
            if(!empty($result)){
                $ccdAllergy = New CcdAllergy;
                $ccdAllergy->patient_id = $user->id;
                $ccdAllergy->allergen_name = $result;
                $ccdAllergy->save();
                echo 'added' . $result . PHP_EOL;
            }

            $result = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::OTHER_CONDITIONS);
            if(!empty($result)){
                $ccdProblem = New CcdProblem;
                $ccdProblem->patient_id = $user->id;
                $ccdProblem->name = $result;
                $ccdProblem->save();
                echo 'added' . $result . PHP_EOL;
            }

            $result = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::MEDICATION_LIST);
            if(!empty($result)){
                $ccdMedication = New CcdMedication;
                $ccdMedication->patient_id = $user->id;
                $ccdMedication->name = $result;
                $ccdMedication->save();
                echo 'added' . $result . PHP_EOL;
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
        //
    }
}
