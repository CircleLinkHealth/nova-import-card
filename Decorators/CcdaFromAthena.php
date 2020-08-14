<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\DB;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;

class CcdaFromAthena implements MedicalRecordDecorator
{
    /**
     * @var AthenaApiImplementation
     */
    protected $api;
    protected $ccda;
    protected $patientUser;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->api = $athenaApiImplementation;
    }

    public function decorate(
        EligibilityJob $eligibilityJob
    ): EligibilityJob {
        $eligibilityJob->loadMissing(['targetPatient.ccda', 'batch.practice']);

        if ( ! $eligibilityJob->targetPatient) {
            if ( ! $this->attemptCreateTargetPatient($eligibilityJob)) {
                return $eligibilityJob;
            }
        }

        // We already have a parsed CCDA, so nothing else to do here
        if ( ! empty(optional($eligibilityJob->targetPatient->ccda)->json)) {
            return $eligibilityJob;
        }

        // We have a CCDA, but it's not parsed. Attempt to parse it.
        if ($eligibilityJob->targetPatient->ccda && $parsed = $eligibilityJob->targetPatient->ccda->bluebuttonJson(true)) {
            return $eligibilityJob;
        }

        // If we could not parse th existing CCDA, fetch a new one from Athena
        $ccda    = AthenaEligibilityCheckableFactory::getCCDFromAthenaApi($eligibilityJob->targetPatient);
        $decoded = $ccda->bluebuttonJson(true);

        return $eligibilityJob;
    }

    /**
     * @param mixed $ccda
     *
     * @return CcdaFromAthena
     */
    public function setCcda(?Ccda $ccda)
    {
        $this->ccda = $ccda;

        return $this;
    }

    /**
     * @param mixed $patientUser
     *
     * @return CcdaFromAthena
     */
    public function setPatientUser(User $patientUser)
    {
        $this->patientUser = $patientUser;

        return $this;
    }

    private function attemptCreateTargetPatient(EligibilityJob &$eligibilityJob)
    {
        if ($eligibilityJob->targetPatient) {
            return;
        }
        if ( ! CcdaImporterWrapper::isAthenaPractice($eligibilityJob->batch->practice->id)) {
            return;
        }
        $nextAppointment = collect(($this->api->getPatientAppointments(
            $athenaPracticeId = $eligibilityJob->batch
                ->practice
                ->external_id,
            $eligibilityJob->patient_mrn,
            true
        )['appointments'] ?? []))->sortBy('date')->first();

        if ( ! $nextAppointment) {
            return;
        }

        $args = [
            'practice_id'        => $eligibilityJob->batch->practice_id,
            'batch_id'           => $eligibilityJob->batch_id,
            'eligibility_job_id' => $eligibilityJob->id,
            'ehr_id'             => $eligibilityJob->batch->practice->ehr_id,
            'ehr_patient_id'     => $eligibilityJob->patient_mrn,
            'ehr_practice_id'    => $athenaPracticeId,
            'ehr_department_id'  => $nextAppointment['departmentid'],
        ];

        if ($this->patientUser) {
            $args['user_id'] = $this->patientUser->id;
        }

        if ($this->ccda) {
            $args['ccda_id'] = $this->ccda->id;
        }

        $targetPatient = TargetPatient::create($args);

        DB::commit();

        $eligibilityJob->load(['targetPatient.ccda']);

        return $targetPatient;
    }
}
