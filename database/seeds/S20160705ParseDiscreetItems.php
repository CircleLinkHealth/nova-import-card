<?php

use App\Models\CCD\Ccda;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\CpmMisc;
use App\Services\CPM\CpmMiscService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class S20160705ParseDiscreetItems extends Seeder
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function run()
    {
        /*
        DB::table('ccd_problems')->truncate();
        DB::table('ccd_medications')->truncate();
        DB::table('ccd_allergies')->truncate();
        */

        // RERUN ITEM MIGRATION
        // add approval meta to patient info
        $users = User::orderBy('ID', 'Desc')->get();
        echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
        $i = 0;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            $existingItem = CcdAllergy::where('patient_id', '=', $user->ID)->first();
            if(!empty($existingItem)){
                echo 'Allergen existing item ID: ' . $existingItem->id . PHP_EOL;
                $list = preg_split("/\\r\\n|\\r|\\n/", $existingItem->allergen_name);
                if(!empty($list)) {
                    foreach($list as $listItem) {
                        if(strlen($listItem) > 3) {
                            $listItem = rtrim($listItem, "; \t\n");
                            $ccdAllergy = New CcdAllergy;
                            $ccdAllergy->patient_id = $user->ID;
                            $ccdAllergy->allergen_name = $listItem;
                            $ccdAllergy->save();
                            echo 'Added Allergen: ' . $listItem . PHP_EOL;
                        }
                    }
                }
                $existingItem->delete();
            }


            $existingItem = CcdProblem::where('patient_id', '=', $user->ID)->first();
            if(!empty($existingItem)){
                echo 'Problem existing item ID: ' . $existingItem->id . PHP_EOL;
                $list = preg_split("/\\r\\n|\\r|\\n/", $existingItem->name);
                if(!empty($list)) {
                    foreach($list as $listItem) {
                        if(strlen($listItem) > 3) {
                            $listItem = rtrim($listItem, "; \t\n");
                            $ccdProblem = New CcdProblem;
                            $ccdProblem->patient_id = $user->ID;
                            $ccdProblem->name = $listItem;
                            $ccdProblem->save();
                            echo 'Added Problem: ' . $listItem . PHP_EOL;
                        }
                    }
                }
                $existingItem->delete();
            }


            $existingItem = CcdMedication::where('patient_id', '=', $user->ID)->first();
            if(!empty($existingItem)){
                echo 'Medication existing item ID: ' . $existingItem->id . PHP_EOL;
                $list = preg_split("/\\r\\n|\\r|\\n/", $existingItem->name);
                if(!empty($list)) {
                    foreach($list as $listItem) {
                        if(strlen($listItem) > 3) {
                            $listItem = rtrim($listItem, "; \t\n");
                            $ccdMed = New CcdMedication;
                            $ccdMed->patient_id = $user->ID;
                            $ccdMed->name = $listItem;
                            $ccdMed->save();
                            echo 'Added Medication: ' . $listItem . PHP_EOL;
                        }
                    }
                }
                $existingItem->delete();
            }
        }
    }
}
