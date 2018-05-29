<?php

use Illuminate\Database\Seeder;
use App\CarePlan;

class RefactoringToEnrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $carePlans = CarePlan::with('patient')->whereStatus('to_enroll')->get();

        foreach ($carePlans as $carePlan){
            $patient = $carePlan->patient;

            if ($patient){
                $patient->ccm_status = 'to_enroll';
                $patient->save();
            }

            $carePlan->status = 'g0506';
            $carePlan->save();

        }
    }
}
