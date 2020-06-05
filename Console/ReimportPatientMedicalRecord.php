<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\MedicalRecordFactory;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CsvWithJsonMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Notifications\PatientNotReimportedNotification;
use CircleLinkHealth\Eligibility\Notifications\PatientReimportedNotification;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        /** @var User $user */
        $user = $this->getUser();

        if ( ! $user) {
            $this->error('User not found');

            return;
        }

        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id}");

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
        if (in_array($user->primaryPractice->name, ['diabetes-texas-pa'])) {
            $ccda = Ccda::where('practice_id', $user->primaryPractice->id)->where('patient_first_name', $user->first_name)->where('patient_last_name', $user->last_name)->where('patient_mrn', 'like', "%{$user->getMRN()}")->first();

            if ($ccda) {
                $ccda->patient_id = $user->id;
                $ccda->save();

                return;
            }

            $eJ = EligibilityJob::whereHas('batch', function ($q) use ($user) {
                $q->where('practice_id', $user->primaryPractice->id);
            })->where('patient_first_name', $user->first_name)->where('patient_last_name', $user->last_name)->where('patient_mrn', 'like', "%{$user->getMRN()}")->first();

            if ( ! $eJ) {
                return;
            }
            $mr = new CsvWithJsonMedicalRecord(
                tap(
                    sanitize_array_keys($eJ->data),
                    function ($data) use ($eJ) {
                        $eJ->data = $data;
                        $eJ->save();
                    }
                )
            );

            $ccda = Ccda::create(
                [
                    'source'      => $mr->getType(),
                    'json'        => $mr->toJson(),
                    'practice_id' => (int) $user->program_id,
                    'patient_id'  => $user->id,
                    'mrn'         => $user->getMRN(),
                ]
            );
            \Log::debug(
                "ReimportPatientMedicalRecord:user_id:{$user->id} Created CCDA ccda_id:{$ccda->id}:ln:".__LINE__
            );
        }

        if (in_array($user->primaryPractice->name, ['marillac-clinic-inc', 'calvary-medical-clinic'])) {
            $this->warn(
                "ReimportPatientMedicalRecord:user_id:{$user->id}:enrollee_id:{$this->getEnrollee($user)->id} Running 'csv-with-json' decorator:ln:".__LINE__
            );
            \Log::debug(
                "ReimportPatientMedicalRecord:user_id:{$user->id}:enrollee_id:{$this->getEnrollee($user)->id} Running 'csv-with-json' decorator:ln:".__LINE__
            );

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
                \Log::debug(
                    "ReimportPatientMedicalRecord:user_id:{$user->id} Created CCDA ccda_id:{$ccda->id}:ln:".__LINE__
                );
            }
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
            \Log::debug(
                "ReimportPatientMedicalRecord:user_id:{$user->id} Fetched latest CCDA ccda_id:{$ccda->id}:ln:".__LINE__
            );

            return $ccda;
        }

        $this->correctMrnIfWrong($user);

        if ($ccda = $this->getCcdaFromMrn($user->patientInfo->mrn_number, $user->program_id)) {
            return $ccda;
        }

        if ($ccda = $this->getCcdaFromAthenaAPI($user)) {
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

    private function correctMrnIfWrong(User $user)
    {
        if (empty($user->patientInfo->mrn_number) && ! empty($this->getEnrollee($user)->mrn)) {
            \Log::debug(
                "ReimportPatientMedicalRecord:user_id:{$user->id} Saving mrn from enrollee_id:{$this->getEnrollee($user)->id}:ln:".__LINE__
            );

            $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
            $user->patientInfo->save();
        }

        if ($user->patientInfo->mrn_number !== $this->getEnrollee($user)->mrn) {
            if (
                ($this->getEnrollee($user)->first_name == $user->first_name)
                && ($this->getEnrollee($user)->last_name == $user->last_name)
                && ($this->getEnrollee($user)->dob->isSameAs($user->patientInfo->birth_date))
            ) {
                \Log::debug(
                    "ReimportPatientMedicalRecord:user_id:{$user->id} Saving mrn from enrollee_id:{$this->getEnrollee($user)->id}:ln:".__LINE__
                );

                $user->patientInfo->mrn_number = $this->getEnrollee($user)->mrn;
                $user->patientInfo->save();
            }
        }
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

        $this->warn("Fetching CCDA from AthenaAPI for user:$user->id");

        return AthenaEligibilityCheckableFactory::getCCDFromAthenaApi($user->ehrInfo);
    }

    private function getCcdaFromMrn($mrn, int $practiceId)
    {
        if ( ! $mrn || ! $practiceId) {
            return null;
        }

        if ( ! $this->ccda) {
            $this->ccda = Ccda::where('practice_id', $practiceId)->where(
                function ($q) use ($mrn) {
                    $q->where('patient_id', $this->argument('patientUserId'))
                        ->orWhere('patient_mrn', $mrn);
                }
            )->first();
        }

        return $this->ccda;
    }

    private function getEnrollee(User $user): Enrollee
    {
        if ( ! $this->enrollee) {
            \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Fetching enrollee ln:".__LINE__);

            $this->enrollee = Enrollee::where(
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
            )->has('eligibilityJob')->orderByDesc('id')->firstOrFail();
            \Log::debug(
                "ReimportPatientMedicalRecord:user_id:{$user->id} Fetched enrollee_id:{$this->enrollee->id}:ln:".__LINE__
            );
        }

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
        $this->warn("ReimportPatientMedicalRecord:user_id:{$user->id} Importing CCDA:{$ccda->id}:ln:".__LINE__);
        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} Importing CCDA:{$ccda->id}:ln:".__LINE__);

        if ( ! $ccda->patient_id) {
            $ccda->patient_id = $user->id;
            $ccda->save();
        }

        try {
            $ccda->import($this->getEnrollee($user));
        } catch (ModelNotFoundException $e) {
            $ccda->import();
        }

        \Log::debug("ReimportPatientMedicalRecord:user_id:{$user->id} CcdaId:{$ccda->id}:ln:".__LINE__);
    }

    private function notifyFailure(User $user)
    {
        $this->warn("Could not find any records for user:{$user->id}.");
        \Log::debug("Could not find any records for user:{$user->id}");

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

    private function reimport(User $user): bool
    {
        if ( ! $user->hasCcda()) {
            $this->attemptCreateCcdaFromMrTemplate($user);
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
