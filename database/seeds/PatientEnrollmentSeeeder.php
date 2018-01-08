<?php

use Illuminate\Database\Seeder;
use App\CarePlan;
use App\CareplanAssessment;

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
        $this->createPatientThatDidNotEnroll();
        $this->createPatientThatEnrolledAndProviderSkippedAssessment();
        $this->createPatientThatEnrolledAndProviderPerformsAssessmentAndPrints();
        $this->createPatientThatEnrolledAndProviderPerformsAssessmentAndForgetsToPrint();
    }

    /**
    * Test Case 1: Patient did NOT Enroll
    */
    function createPatientThatDidNotEnroll() {
        $id = 335;
        $careplans = CarePlan::where([ 'user_id' => $id ]);
        $careplans->update([
            'provider_approver_id' => null,
            'status' => $this->PATIENT_REJECTED
        ]);
    }

    /**
    * Test Case 2: Patient enrolled and MD skips assessment
    */
    function createPatientThatEnrolledAndProviderSkippedAssessment() {
        $id = 336;
        $careplans = CarePlan::where([ 'user_id' => $id ]);
        $careplans->get()->map(function ($c) {
            $c->assessment()->delete();
        });
        $careplans->update([
            'provider_approver_id' => null,
            'status' => $this->TO_ENROLL
        ]);
    }


    /**
    * Test Case 2: Patient enrolled and MD performs assessment and prints care plan
    */
    function createPatientThatEnrolledAndProviderPerformsAssessmentAndPrints() {
        $id = 337;
        $provider_id = 322;
        $careplans = CarePlan::where([ 'user_id' => $id ]);
        $careplans->get()->map(function ($c) use ($provider_id) {
            if ($c->assessment()->count() == 0) {
                $c->assessment()->create([
                    'provider_approver_id' => $provider_id,
                    'diabetes_screening_interval' => 'Every 6 months'
                ]);
            }
        });
        $careplans->update([
            'qa_approver_id' => $provider_id,
            'provider_approver_id' => $provider_id,
            'status' => $this->PROVIDER_APPROVED,
            'last_printed' => '2018-01-08 17:36:03'
        ]);
    }


    /**
    * Test Case 4: Patient enrolled and MD performs assessment and forgets to print care plan
    */
    function createPatientThatEnrolledAndProviderPerformsAssessmentAndForgetsToPrint() {
        $id = 342;
        $provider_id = 322;
        $careplans = CarePlan::where([ 'user_id' => $id ]);
        $careplans->get()->map(function ($c) use ($provider_id) {
            if ($c->assessment()->count() == 0) {
                $c->assessment()->create([
                    'provider_approver_id' => $provider_id,
                    'diabetes_screening_interval' => 'Every 6 months'
                ]);
            }
        });
        $careplans->update([
            'qa_approver_id' => $provider_id,
            'provider_approver_id' => $provider_id,
            'status' => $this->PROVIDER_APPROVED,
            'last_printed' => null
        ]);
    }
}
