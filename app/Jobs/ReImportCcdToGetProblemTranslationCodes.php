<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\Problems;
use CircleLinkHealth\CarePlanModels\Entities\Problem;
use CircleLinkHealth\CarePlanModels\Entities\Ccda;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReImportCcdToGetProblemTranslationCodes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $ccda;
    private $logger;
    private $patient;
    private $repo;

    /**
     * Create a new job instance.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
        $this->repo    = new CCDImporterRepository();

        $this->ccda = Ccda::select(['id', 'patient_id', 'xml'])
            ->where('patient_id', '=', $this->patient->id)
            ->first();

        ProblemImport::where('medical_record_id', '=', $this->ccda->id)
            ->where('medical_record_type', '=', Ccda::class)
            ->delete();

        ProblemLog::where('medical_record_id', '=', $this->ccda->id)
            ->where('medical_record_type', '=', Ccda::class)
            ->delete();

        Problem::where('patient_id', '=', $this->patient->id)
            ->delete();

        $this->logger = new CcdaSectionsLogger($this->ccda);

        $this->logger->logProblemsSection();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $problemsImporter = new Problems();
        $problemsList     = $problemsImporter->import($this->ccda->id, Ccda::class, $this->ccda->importedMedicalRecord());

        $carePlanHelper = new CarePlanHelper($this->patient, $this->ccda->importedMedicalRecord());
        $carePlanHelper->storeProblemsList()
            ->storeProblemsToMonitor();
    }
}
