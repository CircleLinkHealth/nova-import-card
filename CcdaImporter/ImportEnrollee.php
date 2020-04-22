<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use App\Nova\Actions\ClearAndReimportCcda;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\Log;

class ImportEnrollee
{
    public static function import(Enrollee $enrollee)
    {
        $static = new static();

        //verify it wasn't already imported
        if ($enrollee->user_id) {
            /** @var User|null $patientUser */
            $patientUser = $static->handleExistingUser($enrollee);

            if ( ! is_null($patientUser)) {
                return $patientUser;
            }
        }

        /** @var ImportService $importService */
        $importService = app(ImportService::class);

        //import ccda
        if ($importService->isCcda($enrollee->medical_record_type)) {
            return $importService->importExistingCcda($enrollee->medical_record_id, $enrollee);
        }

        //import from AthenaAPI
        if ($enrollee->targetPatient) {
            return $static->importTargetPatient($enrollee);
        }

        //import from eligibility jobs
        $job = $static->eligibilityJob($enrollee);

        if ($job) {
            $importedUsingMrn = $static->importCcdUsingMrnFromEligibilityJob($job, $enrollee);

            if (false !== $importedUsingMrn) {
                return $importedUsingMrn;
            }

            $static->importFromEligibilityJob($enrollee, $job);
        }

        Log::error("This should never be reached. enrollee: $enrollee->id");
    }

    private function eligibilityJob(Enrollee $enrollee)
    {
        if ($enrollee->eligibilityJob) {
            return $enrollee->eligibilityJob;
        }
        $hash = $enrollee->practice->name.$enrollee->first_name.$enrollee->last_name.$enrollee->mrn.$enrollee->city.$enrollee->state.$enrollee->zip;

        return EligibilityJob::whereHash($hash)->first();
    }

    private function enrolleeAlreadyImported(Enrollee $enrollee)
    {
        $link = route('patient.careplan.print', [$enrollee->user_id]);
        $this->log("Eligible patient with ID {$enrollee->id} has already been imported. See $link");
    }

    private function enrolleeMedicalRecordImported(Enrollee $enrollee)
    {
        $link = route('import.ccd.remix');
        $this->log("Just imported the CCD of Eligible Patient ID {$enrollee->id}. Please visit $link");
    }

    private function handleExistingUser(Enrollee $enrollee): ?User
    {
        if ( ! $enrollee->user_id) {
            return null;
        }

        $user = User::withTrashed()->find($enrollee->user_id);

        if ( ! $user) {
            $enrollee->user_id = null;
            $enrollee->save();

            return null;
        }

        if (is_null($user->deleted_at)) {
            $this->enrolleeAlreadyImported($enrollee);

            return $user;
        }

        if ($user->restore()) {
            ClearAndReimportCcda::for($user->id, auth()->id(), 'call');

            $this->enrolleeMedicalRecordImported($enrollee);

            return $user;
        }
    }

    /**
     * @return bool|\stdClass
     */
    private function importCcdUsingMrnFromEligibilityJob(EligibilityJob $job, Enrollee $enrollee)
    {
        $mrn = $job->data['mrn_number'] ?? $job->data['mrn'] ?? $job->data['patient_id'] ?? $job->data['internal_id'] ?? null;

        if ( ! $mrn) {
            return false;
        }

        $ccda = Ccda::whereBatchId($job->batch_id)->whereMrn($mrn)->first();

        if ( ! $ccda) {
            return false;
        }

        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $enrollee->save();

        return app(ImportService::class)->importExistingCcda($ccda->id);
    }

    private function importFromEligibilityJob(Enrollee $enrollee, EligibilityJob $job)
    {
        $mr = new CsvWithJsonMedicalRecord($job->data);
        
        $ccda = Ccda::create([
            'json'        => $mr->toJson(),
            'mrn'         => $mr->getMrn(),
            'practice_id' => $enrollee->practice_id,
        ]);

        $enrollee->medical_record_type = get_class($ccda);
        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->save();

        $ccda = $ccda->import($enrollee);

        $this->enrolleeMedicalRecordImported($enrollee);
    }

    private function importTargetPatient(Enrollee $enrollee): ?User
    {
        $url = route(
            'import.ccd.remix',
            'Click here to Create and a CarePlan and review.'
        );

        $athenaApi = app(AthenaApiImplementation::class);

        $ccdaExternal = $athenaApi->getCcd(
            $enrollee->targetPatient->ehr_patient_id,
            $enrollee->targetPatient->ehr_practice_id,
            $enrollee->targetPatient->ehr_department_id
        );

        if ( ! isset($ccdaExternal[0])) {
            $this->log("Could not retrieve CCD from Athena for eligible patient id $enrollee->id");

            return null;
        }

        $ccda = Ccda::create(
            [
                'practice_id' => $enrollee->practice_id,
                'vendor_id'   => 1,
                'xml'         => $ccdaExternal[0]['ccda'],
            ]
        );

        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $enrollee->save();
        $imported = $ccda->import();

        $this->enrolleeMedicalRecordImported($enrollee);

        return $imported->patient;
    }

    private function log($message)
    {
        \Log::warning($message);

        sendSlackMessage('#parse_enroll_import', $message);
    }
}
