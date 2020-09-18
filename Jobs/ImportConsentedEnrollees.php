<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\ImportEnrollee;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportConsentedEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityBatch
     */
    private $batch;
    /**
     * @var array
     */
    private $enrolleeIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $enrolleeIds, EligibilityBatch $batch = null)
    {
        $this->enrolleeIds = $enrolleeIds;
        $this->batch       = $batch;
    }

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        Enrollee::whereIn('id', $this->enrolleeIds)
            ->with(['targetPatient', 'practice', 'eligibilityJob'])
            ->chunkById(
                10,
                function ($enrollees) {
                    $enrollees->each(
                        function ($enrollee) {
                            ImportEnrollee::import($enrollee);
                        }
                    );
                }
            );
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        $ids = implode(',', $this->enrolleeIds);

        return ['importconsentedenrollees', 'enrollees:'.$ids];
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

    private function getDefaultPatientReimportNotifiableId(): ?int
    {
        return AppConfig::pull('default_patient_reimport_notifiable_id', null);
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
            Artisan::call(
                ReimportPatientMedicalRecord::class,
                [
                    'patientUserId'   => $user->id,
                    'initiatorUserId' => auth()->id(),
                    '--flush-ccd'     => true,
                ]
            );

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
        if ( ! $enrollee->user_id) {
            $user = (new CCDImporterRepository())->createRandomUser(
                new DemographicsImport(
                    [
                        'email'      => $enrollee->email,
                        'first_name' => $enrollee->first_name,
                        'last_name'  => $enrollee->last_name,
                        'street'     => $enrollee->address,
                        'street2'    => $enrollee->address_2,
                        'city'       => $enrollee->city,
                        'state'      => $enrollee->state,
                        'zip'        => $enrollee->zip,
                    ]
                ),
                ImportedMedicalRecord::firstOrNew([
                    'patient_id'          => $enrollee->user_id,
                    'practice_id'         => $enrollee->practice_id,
                    'medical_record_id'   => $enrollee->medical_record_id,
                    'medical_record_type' => $enrollee->medical_record_type,
                ])
            );

            $enrollee->user_id = $user->id;
            $enrollee->save();
        }

        Artisan::call(
            ReimportPatientMedicalRecord::class,
            [
                'patientUserId'   => $user->id,
                'initiatorUserId' => optional(auth()->user())->isCareAmbassador() ? $this->getDefaultPatientReimportNotifiableId() : auth()->id(),
                '--flush-ccd'     => true,
            ]
        );

        $this->enrolleeMedicalRecordImported($enrollee);
    }

    private function importTargetPatient(Enrollee $enrollee)
    {
        $url = route(
            'import.ccd.remix',
            'Click here to Create and a CarePlan and review.'
        );

        $athenaApi = app(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);

        $ccdaExternal = $athenaApi->getCcd(
            $enrollee->targetPatient->ehr_patient_id,
            $enrollee->targetPatient->ehr_practice_id,
            $enrollee->targetPatient->ehr_department_id
        );

        if ( ! isset($ccdaExternal[0])) {
            $this->log("Could not retrieve CCD from Athena for eligible patient id $enrollee->id");

            return;
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
        $imported                      = $ccda->import();
        $enrollee->save();

        $this->enrolleeMedicalRecordImported($enrollee);
    }

    private function log($message)
    {
        \Log::warning($message);

        sendSlackMessage('#parse_enroll_import', $message);
    }
}
