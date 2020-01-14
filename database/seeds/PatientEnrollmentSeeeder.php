<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use CircleLinkHealth\CarePlanModels\Entities\CarePlan;
use App\CareplanAssessment;
use App\Note;
use CircleLinkHealth\Customer\Entities\Patient;

/**
* Seeder to create the four test patients Raph needs to test the G0506 flow
*/
class PatientEnrollmentSeeeder extends Seeder
{
    private $TO_ENROLL = 'to_enroll';
    private $PROVIDER_APPROVED = 'provider_approved';
    private $PATIENT_REJECTED = 'patient_rejected';
    private $G0506 = 'g0506';

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
        })->map(function ($careplan) {
            if ($careplan) {
                $careplan->update([
                    'provider_approver_id' => null,
                    'status' => $this->G0506
                ]);
                Patient::where([ 'user_id' => $careplan->user_id ])->update([
                    'ccm_status' => $this->TO_ENROLL
                ]);
                CareplanAssessment::where([ 'careplan_id' => $careplan->user_id ])->delete();
                Note::where([ 'patient_id' => $careplan->user_id, 'type' => 'Enrollment' ])
                    ->orWhere([ 'patient_id' => $careplan->user_id, 'type' => 'Edit Assessment' ])->delete();
                $this->command->info('enroll: ' . $careplan->user_id);
            }
        });
    }
}
