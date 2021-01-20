<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\MedicalRecordFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Notifications\PatientNotReimportedNotification;
use CircleLinkHealth\Eligibility\Notifications\PatientReimportedNotification;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ReimportPatientMedicalRecord extends Command
{
    private const ATTEMPTS = 2;
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
    protected $signature = 'patient:recreate {patientUserId} {initiatorUserId?} {--clear} {--without-transaction}';
    /**
     * @var Ccda
     */
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

    public static function for(int $patientUserId, ?int $notifiableUserId, string $method = 'queue', array $args = []): void
    {
        Artisan::$method(
            ReimportPatientMedicalRecord::class,
            array_merge([
                'patientUserId'   => $patientUserId,
                'initiatorUserId' => $notifiableUserId,
            ], $args)
        );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->clearState();

        /** @var User $user */
        $user = $this->getUser();

        if ( ! $user) {
            $this->error('User not found');

            return;
        }

        $this->log("Running for User[{$user->id}]");

        if ($this->option('without-transaction')) {
            $this->reimport($user);

            return;
        }

        DB::transaction(function () use ($user) {
            $this->reimport($user);
        }, self::ATTEMPTS);
    }

    private function attemptCreateCcdaFromMrTemplate(User $user)
    {
        if (in_array($user->primaryPractice->name, ['marillac-clinic-inc', 'calvary-medical-clinic']) && ! empty($this->getEnrollee($user)) && ! empty($this->getEnrollee($user)->eligibilityJob)) {
            $this->warn(
                $msg = "User[{$user->id}] Enrollee[{$this->getEnrollee($user)->id}]. Running 'csv-with-json' decorator."
            );
            $this->log($msg);

            $mr = new CsvWithJsonMedicalRecord(
                tap(
                    sanitize_array_keys($this->getEnrollee($user)->eligibilityJob->data),
                    function ($data) use ($user) {
                        $this->getEnrollee($user)->eligibilityJob->data = $data;
                        $this->getEnrollee($user)->eligibilityJob->save();
                    }
                )
            );

            $mrn = $user->patientInfo->mrn_number ?? $this->getEnrollee(
                $user
            )->eligibilityJob->data['mrn'] ?? null;

            if ($mrn) {
                $ccda = $this->getCcdaFromMrn($user->patientInfo->mrn_number, $user->program_id);
            }

            if (empty($ccda ?? null)) {
                $ccda = Ccda::create(
                    [
                        'source'      => $mr->getType(),
                        'json'        => $mr->toJson(),
                        'practice_id' => (int) $user->program_id,
                        'patient_id'  => $user->id,
                        'mrn'         => $mrn,
                    ]
                );
                $this->log("User[{$user->id}] Created CCDA[{$ccda->id}]");
            }

            return $ccda;
        }

        if ($mr = MedicalRecordFactory::create($user, null)) {
            $ccda = Ccda::create(
                [
                    'source'      => $mr->getType(),
                    'json'        => $mr->toJson(),
                    'practice_id' => (int) $user->program_id,
                    'patient_id'  => $user->id,
                ]
            );
            $this->log("User[{$user->id}] Created CCDA[{$ccda->id}]");

            return $ccda;
        }

        return null;
    }

    private function attemptDecorator(User $user, Ccda $ccda)
    {
        if ($mr = MedicalRecordFactory::create($user, $ccda)) {
            $this->warn("Running '{$user->primaryPractice->name}' decorator");

            return $mr;
        }

        return null;
    }

    private function attemptFetchCcda(User $user)
    {
        if ($ccda = $this->getUser()->latestCcda()) {
            $this->log("User[{$user->id}] Fetched latest CCDA[{$ccda->id}] from DB");

            return $ccda;
        }

        $this->correctMrnIfWrong($user);

        if ($ccda = $this->getCcdaFromMrn($user->patientInfo->mrn_number, $user->program_id)) {
            $this->log("User[{$user->id}] Fetched CCDA[{$ccda->id}] from DB by MRN");

            return $ccda;
        }

        if ($ccda = $this->getCcdaFromAthenaAPI($user)) {
            $this->log("User[{$user->id}] Fetched CCDA[{$ccda->id}] from Athena API");

            return $ccda;
        }
    }

    private function attemptImportCcda(User $user)
    {
        $ccda = $this->attemptFetchCcda($user);

        if ( ! $ccda) {
            return false;
        }

        if ($mr = $this->attemptDecorator($user, $ccda)) {
            if ( ! is_null($mr)) {
                $ccda->json = $mr->toJson();
                $ccda->save();
            }
        }

        $this->importCcdaAndFillCarePlan($ccda, $user);

        $this->notifySuccess($user);

        return true;
    }

    private function clearExistingCarePlanData(User $user)
    {
        if ( ! $this->option('clear')) {
            return;
        }

        $user->ccdMedications()->delete();

        //practices whose careplans do not contain CCDs
        if ( ! in_array($user->primaryPractice->name, ['diabetes-texas-pa'])) {
            $user->ccdProblems()->delete();
        }
        $user->ccdAllergies()->delete();
    }

    /**
     * In the case of AutoApproveValidCarePlansAs where we programmatically call this command on each user in a dataset, the state does not reset when calling this on the next user. Adding this to confirm issue.
     */
    private function clearState()
    {
        $this->ccda     = null;
        $this->enrollee = null;
    }

    private function correctMrnIfWrong(User $user)
    {
        if (empty($this->getEnrollee($user))) {
            return;
        }

        if (empty($user->patientInfo->mrn_number) && ! empty($this->getEnrollee($user)->mrn)) {
            $this->log("User[{$user->id}] Saving mrn from Enrollee[{$this->getEnrollee($user)->id}]");

            $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
            $user->patientInfo->save();
        }

        if ($user->patientInfo->mrn_number !== $this->getEnrollee($user)->mrn) {
            if (
                ($this->getEnrollee($user)->first_name == $user->first_name)
                && ($this->getEnrollee($user)->last_name == $user->last_name)
                && ($this->getEnrollee($user)->dob->isSameAs($user->patientInfo->birth_date))
            ) {
                $this->log("User[{$user->id}] Saving mrn from Enrollee[{$this->getEnrollee($user)->id}]");

                $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
                $user->patientInfo->save();
            }
        }
    }

    private function enrolleeQuery(User $user)
    {
        return Enrollee::where(
            [
                ['mrn', '=', $user->getMRN()],
                ['practice_id', '=', $user->program_id],
                ['first_name', '=', $user->first_name],
                ['last_name', '=', $user->last_name],
            ]
        )->where(
            function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            }
        )->with(
            'eligibilityJob'
        );
    }

    /**
     * @throws \Exception
     */
    private function getCcdaFromAthenaAPI(User $user): ?MedicalRecord
    {
        $user->loadMissing('ehrInfo');

        if ( ! $user->ehrInfo) {
            return null;
        }

        $this->warn("Fetching CCDA from AthenaAPI for User[$user->id]");

        return AthenaEligibilityCheckableFactory::getCCDFromAthenaApi($user->ehrInfo);
    }

    private function getCcdaFromMrn($mrn, int $practiceId)
    {
        if ( ! $mrn || ! $practiceId) {
            return null;
        }

        if ( ! $this->ccda) {
            $this->ccda = Ccda::where('practice_id', $practiceId)
                ->whereNotNull('practice_id')
                ->whereNotNull('patient_mrn')
                ->where(
                    function ($q) use ($mrn) {
                        $q->where([
                            ['patient_id', '=', $this->argument('patientUserId')],
                            ['patient_id', 'is not', null],
                        ])
                            ->orWhere('patient_mrn', $mrn);
                    }
                )
                ->first();
        }

        return $this->ccda;
    }

    private function getEnrollee(User $user): ?Enrollee
    {
        if ($this->enrollee) {
            $this->log("User[{$user->id}] Enrollee[{$this->enrollee->id}] had already been fetched. Returning Enrollee.");

            return $this->enrollee;
        }
        $this->log("User[{$user->id}] Fetching Enrollee");

        $enrollees = $this->enrolleeQuery($user)->get();

        if ($enrollees->isEmpty()) {
            return null;
        }

        if ($e = $enrollees->where('status', Enrollee::CONSENTED)->first()) {
            $this->enrollee = $e;
        } elseif ($e = $enrollees->where('status', Enrollee::ENROLLED)->first()) {
            $this->enrollee = $e;
        } elseif ($e = $enrollees->where('status', Enrollee::TO_CALL)->first()) {
            $this->enrollee = $e;
        } else {
            $this->enrollee = $enrollees->first();
        }
        $this->log("User[{$user->id}] Fetched Enrollee[{$this->enrollee->id}]");

        return $this->enrollee;
    }

    private function getUser()
    {
        return User::with(
            [
                'patientInfo',
                'primaryPractice',
            ]
        )->find($this->argument('patientUserId'));
    }

    private function importCcdaAndFillCarePlan(Ccda $ccda, User $user)
    {
        $this->warn($msg = "User[{$user->id}] Importing CCDA[{$ccda->id}]");
        $this->log($msg);

        if ( ! $ccda->patient_id) {
            $this->log("User[{$user->id}] CCDA[{$ccda->id}] has no patient_id. Bailing.");

            $ccda->patient_id = $user->id;
            $ccda->save();
        }

        if ($enrollee = $this->getEnrollee($user)) {
            $this->log("Importing User[{$user->id}] CCDA[{$ccda->id}] Enrollee[{$enrollee->id}]");
            $ccda->import($enrollee);
        } else {
            $this->log("Importing User[{$user->id}] CCDA[{$ccda->id}]");
            $ccda->import();
        }

        $this->log("Finished Importing User[{$user->id}] CCDA[{$ccda->id}]");
    }

    private function log(string $string)
    {
        $backtrace = debug_backtrace()[0];
        \Log::debug($backtrace['class'].':'.$backtrace['line']."\n $string");
    }

    private function notifyFailure(User $user)
    {
        $this->warn($msg = "Could not find any CCDAs for User[{$user->id}].");
        $this->log($msg);

        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying of failure user:$initiatorId");
//            User::findOrFail($initiatorId)->notify(new PatientNotReimportedNotification($user->id));
        }
    }

    private function notifySuccess(User $user)
    {
        if ($initiatorId = $this->argument('initiatorUserId')) {
            $this->warn("Notifying user:$initiatorId");
//            User::findOrFail($initiatorId)->notify(new PatientReimportedNotification($user->id));
        }
    }

    private function reimport(User $user): bool
    {
        if ( ! $user->ccdas()->exists()) {
            if ($this->getEnrollee($user) && $ccda = Ccda::where('practice_id', $this->enrollee->practice_id)
                ->where('patient_mrn', $this->enrollee->mrn)
                ->where('patient_dob', $this->enrollee->dob->toDateString())->first()) {
                $this->enrollee->medical_record_id = $ccda->id;
                $this->enrollee->save();
            } else {
                $this->attemptCreateCcdaFromMrTemplate($user);
            }
        }

        $this->clearExistingCarePlanData($user);

        if ($this->attemptImportCcda($user)) {
            $this->line('Ccda imported.');

            return true;
        }

        $this->notifyFailure($user);

        return false;
    }
}