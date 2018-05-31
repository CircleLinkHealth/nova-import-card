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
        $carePlans = CarePlan::with('patient')->whereIn('status', ['to_enroll', 'patient_rejected'])->get();

        foreach ($carePlans as $carePlan){
            $patient = $carePlan->patient;
            $status  = $carePlan->status;

            if ($patient){
                $patient->ccm_status = $status;
                $patient->save();
            }

            $carePlan->status = 'g0506';
            $carePlan->save();

        }
    }
}
