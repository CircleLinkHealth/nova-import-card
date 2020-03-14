<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\PatientNotReimportedNotification;
use App\Notifications\PatientReimportedNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CommonwealthMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\MarillacMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class ReimportPatientMedicalRecord extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport patient data from one medical record decided by this command. To be used for patient that did not import correctly.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:recreate {patientUserId} {initiatorUserId?}';
    private $ccda;
    /**
     * @var Enrollee
     */
    private $enrollee;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = $this->getUser();

        if ( ! $user) {
            $this->error('User not found');

            return;
        }

        if ($this->attemptTemplate($user)) {
            return;
        }

        if ($this->attemptCcda($user)) {
            return;
        }

        $this->notifyFailure($user);
    }

    private function attemptCcda(User $user)
    {
        $ccda = $this->getCcdaFromMrn($user->getMRN(), $user->program_id);

        if ( ! $ccda) {
            return false;
        }

        if ($mr = $this->attemptDecorator($user, $ccda)) {
            if ( ! is_null($mr)) {
                $ccda->json = $mr->toJson();
                $ccda->save();
            }
        }

        $this->importCcda($ccda, $user);

        $this->notifySuccess($user);

        return true;
    }

    private function attemptDecorator(User $user, Ccda $ccda)
    {
        if ('commonwealth-pain-associates-pllc' === $user->primaryPractice->name) {
            $this->warn("Running 'commonwealth-pain-associates-pllc' decorator");

            return new CommonwealthMedicalRecord(
                $this->getEnrollee($user)->eligibilityJob->data,
                new CcdaMedicalRecord($ccda->bluebuttonJson())
            );
        }

        return null;
    }

    private function attemptTemplate(User $user)
    {
        if ('marillac-clinic-inc' === $user->primaryPractice->name) {
            $this->warn("Running 'marillac-clinic-inc' decorator");

            $mr = new MarillacMedicalRecord(
                $this->getEnrollee($user)->eligibilityJob->data
            );

            $ccda = $this->getCcdaFromMrn($user->getMRN(), $user->program_id);

            if ( ! $ccda) {
                $ccda = Ccda::create(
                    [
                        'source'      => $mr->getType(),
                        'json'        => $mr->toJson(),
                        'practice_id' => (int) $user->program_id,
                    ]
                );
            }
        }

        return null;
    }

    private function getCcdaFromMrn($mrn, int $practiceId)
    {
        if ( ! $mrn || ! $practiceId) {
            return null;
        }

        if ( ! $this->ccda) {
            $this->ccda = Ccda::wherePracticeId($practiceId)->where(
                'json->demographics->mrn_number',
                $mrn
            )->first();
        }

        return $this->ccda;
    }

    private function getEnrollee(User $user): Enrollee
    {
        if ( ! $this->enrollee) {
            $this->enrollee = Enrollee::whereUserId($user->id)->wherePracticeId($user->program_id)->with(
                'eligibilityJob'
            )->has('eligibilityJob')->first();
        }

        return $this->enrollee;
    }

    private function getUser()
    {
        return User::with(
            [
                'patientInfo',
            ]
        )->find($this->argument('patientUserId'));
    }

    private function importCcda($ccda, User $user)
    {
        $this->warn("Importing CCDA:$ccda->id");
        $ccda->import();

        /**
         * @todo: method below is inefficient. Needs to be optimized.
         */
        /** @var ImportedMedicalRecord $imr */
        $imr = $ccda->importedMedicalRecord();

        if ( ! $imr) {
            $this->warn("Creating IMR for CCDA:$ccda->id");
            $imr = $ccda->createImportedMedicalRecord()->importedMedicalRecord();
        }

        $imr->patient_id = $user->id;
        $imr->save();

        $this->warn("Creating CarePlan from CCDA:$ccda->id");

        $imr->updateOrCreateCarePlan();

        $this->line("Patient $user->id reimported from CCDA $ccda->id");

        $this->getEnrollee($user)->medical_record_id   = $ccda->id;
        $this->getEnrollee($user)->medical_record_type = get_class($ccda);
        $this->getEnrollee($user)->save();
    }

    private function notifyFailure(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying of failure user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientNotReimportedNotification($user->id));
        }
    }

    private function notifySuccess(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying user:$initiatorId");
            User::findOrFail($initiatorId)->notify(new PatientReimportedNotification($user->id));
        }
    }
}
