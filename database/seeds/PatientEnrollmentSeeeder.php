<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use App\CarePlan;
use App\CareplanAssessment;
use App\Note;

/**
* Seeder to create the four test patients Raph needs to test the G0506 flow
*/
class PatientEnrollmentSeeeder extends Seeder
{
    private $TO_ENROLL = 'to_enroll';
    private $PROVIDER_APPROVED = 'provider_approved';
    private $PATIENT_REJECTED = 'patient_rejected';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $patients = new Collection([ 335, 336, 337, 342 ]);
        $patients->map(function ($id) {
            return Careplan::where([ 'user_id' => $id ])->first();
        })->map(function ($patient) {
            if ($patient) {
                Careplan::where([ 'user_id' => $patient->user_id ])->update([
                    'provider_approver_id' => null,
                    'status' => $this->TO_ENROLL
                ]);
                CareplanAssessment::where([ 'careplan_id' => $patient->user_id ])->delete();
                Note::where([ 'patient_id' => $patient->user_id, 'type' => 'Enrollment' ])
                    ->orWhere([ 'patient_id' => $patient->user_id, 'type' => 'Edit Assessment' ])->delete();
            }
        });
    }
}
