<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter;

use CircleLinkHealth\Core\Helpers\StringHelpers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\Log;

class ImportEnrollee
{
    public static function import(Enrollee &$enrollee)
    {
        $static = new static();

        //verify it wasn't already imported
        if ($enrollee->user_id) {
            /** @var User|null $patientUser */
            $patientUserImported = $static->handleExistingUser($enrollee);

            if ( ! is_null($patientUserImported)) {
                return $patientUserImported;
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

        if ($ccda = self::matchCcda($enrollee)) {
            return $importService->importExistingCcda($enrollee->medical_record_id, $enrollee);
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

        //If enrollee is from uploaded CSV from Nova Page,
        //Where we create Enrollees without any other data,
        //so we can consent them and then ask the practice to send us the CCDs
        //It is expected to reach this point, do not throw error
        if (Enrollee::UPLOADED_CSV === $enrollee->source) {
            return;
        }

        if (Enrollee::ENROLLED === $enrollee->status) {
            return;
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
        $msg = "Just imported the CCD of Eligible Patient ID {$enrollee->id}. \nPlease visit ";

        if ($enrollee->user()->exists()) {
            $msg .= route('patient.careplan.print', [$enrollee->user_id]);
            $enrollee->setRelation('user', $enrollee->user ?? $enrollee->fresh('user')->user);
        } else {
            $msg .= route('import.ccd.remix');
        }

        $this->log($msg);
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

        //If user is survey only return null so we can proceed with the importing
        if ($user->isSurveyOnly()) {
            return null;
        }

        if (is_null($user->deleted_at)) {
            $this->enrolleeAlreadyImported($enrollee);

            return $user;
        }

        if ($user->restore()) {
            ReimportPatientMedicalRecord::for($user->id, auth()->id(), 'call', ['--clear' => true]);

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

        $ccdaArgs = [
            'json'        => $mr->toJson(),
            'mrn'         => $mr->getMrn(),
            'practice_id' => $enrollee->practice_id,
        ];

        $enrolleeUser = $enrollee->user;

        if ($enrolleeUser) {
            if ($enrolleeUser->isSurveyOnly()) {
                $ccdaArgs['patient_id']          = $enrolleeUser->id;
                $ccdaArgs['billing_provider_id'] = $enrollee->provider_id;
            }
        }

        $ccda = Ccda::create($ccdaArgs);

        $enrollee->medical_record_type = get_class($ccda);
        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->save();

        //We need to refresh the model before we perform the import, to make sure virtual columns contain the proper data
        //since the model that gets returned from Ccda::create method contains null values fro virtual columns
        $ccda = $ccda->fresh()->import($enrollee);

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
                'xml'         => $ccdaExternal[0]['ccda'],
            ]
        );

        $enrollee->medical_record_id   = $ccda->id;
        $enrollee->medical_record_type = Ccda::class;
        $enrollee->save();
        $imported = $ccda->import($enrollee);

        $this->enrolleeMedicalRecordImported($enrollee);

        return $imported->patient;
    }

    private function log($message)
    {
        \Log::warning($message);

        sendSlackMessage('#parse_enroll_import', $message);
    }

    private static function matchCcda(Enrollee &$enrollee): ?Ccda
    {
        if ($ccda = Ccda::whereNotNull('practice_id')
            ->where('practice_id', $enrollee->practice_id)
            ->where('patient_last_name', $enrollee->last_name)
            ->where('patient_mrn', $enrollee->mrn)
            ->where('patient_dob', $enrollee->dob->toDateString())
            ->first()) {
            $ccdaConcName     = $ccda->patient_last_name.$ccda->patient_first_name;
            $enrolleeConcName = $enrollee->last_name.$enrollee->first_name;

            // Ensure we have either an exact or partial match
            // Sometimes we may have gotten enrollee from a CSV provided by the practice, so the name may differe by a , or .
            if (
                $ccdaConcName === $enrolleeConcName
                || StringHelpers::areSameStringsIfYouCompareOnlyLetters($ccdaConcName, $enrolleeConcName)
            ) {
                $enrollee->medical_record_id = $ccda->id;

                return $ccda;
            }
        }

        return null;
    }
}
