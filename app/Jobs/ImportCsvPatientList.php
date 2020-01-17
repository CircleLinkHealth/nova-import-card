<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DocumentLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use App\Services\CCD\ProcessEligibilityService;
use App\Services\MedicalRecords\ImportService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportCsvPatientList implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    private $patientsArr;
    private $practice;

    /**
     * Create a new job instance.
     *
     * @param mixed $filename
     */
    public function __construct(array $file, $filename)
    {
        $this->patientsArr = $file;

        $this->practice = Practice::whereDisplayName(explode('-', $filename)[0])->first();

        if ( ! $this->practice) {
            dd('Please include the Practice name (as it appears on CPM) in the beginning of the csv filename as such. Demo name - Import List.');
        }
    }

    /**
     * The job failed to process.
     *
     * @param \Exception $exception
     */
    public function failed(\Exception $exception)
    {
        sendSlackMessage('#background-tasks', "Queued job Import CSV patient list failed: ${exception}");
    }

    /**
     * Execute the job.
     *
     * @param ProcessEligibilityService $importService
     */
    public function handle(ImportService $importService)
    {
        foreach ($this->patientsArr as $row) {
            if (isset($row['medical_record_type'], $row['medical_record_id'])) {
                if ($importService->isCcda($row['medical_record_type'])) {
                    $response = $importService->importExistingCcda($row['medical_record_id']);

                    if ($response->success) {
                        $this->replaceWithValuesFromCsv($response->imr, $row);
                    }
                    continue;
                }
            }

            if (isset($row['patient_name'])) {
                $names             = explode(', ', $row['patient_name']);
                $row['first_name'] = $names[0];
                $row['last_name']  = $names[1];
            }

            $importService->createTabularMedicalRecordAndImport($row, $this->practice);
        }

        $url = url('import.ccd.remix');

        sendSlackMessage(
            '#background-tasks',
            "Queued job Import CSV for {$this->practice->display_name} completed! Visit ${url}."
        );
    }

    /**
     * Get the most updated information from the csv (phone numbers, preferred call days/times, provider and so on).
     *
     * @param \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord $importedMedicalRecord
     * @param array                 $row
     */
    public function replaceWithValuesFromCsv(ImportedMedicalRecord $importedMedicalRecord, array $row)
    {
        $demographics = $importedMedicalRecord->demographics;

        $demographics->primary_phone        = $row['primary_phone'] ?? '';
        $demographics->preferred_call_times = $row['preferred_call_times'] ?? '';
        $demographics->preferred_call_days  = $row['preferred_call_days'] ?? '';

        foreach (['cell_phone', 'home_phone', 'work_phone'] as $phone) {
            if ( ! array_key_exists($phone, $row)) {
                continue;
            }

            if ($demographics->{$phone} == $row[$phone]) {
                continue;
            }

            $demographics->{$phone} = $row[$phone];
        }

        $demographics->save();

        if ( ! $importedMedicalRecord->practice_id) {
            $importedMedicalRecord->practice_id = $this->practice->id;
        }

        if ( ! $importedMedicalRecord->location_id) {
            $importedMedicalRecord->location_id = $this->practice->primary_location_id;
        }

        if ( ! $importedMedicalRecord->billing_provider_id && array_key_exists('billing_provider', $row)) {
            $providerName = explode(' ', $row['billing_provider']);

            if (count($providerName) >= 2) {
                $provider = User::whereFirstName($providerName[0])
                    ->whereLastName($providerName[1])
                    ->first();
            }

            if ( ! empty($provider)) {
                $importedMedicalRecord->billing_provider_id = $provider->id;

                if ($provider->locations->first()) {
                    $importedMedicalRecord->location_id = $provider->locations->first()->id;
                }
            }
        }

        $mr = $importedMedicalRecord->medicalRecord();

        if (optional($mr->documents)->isNotEmpty()) {
            DocumentLog::whereIn('id', $mr->document->pluck('id')->all())
                ->update([
                    'location_id'         => $importedMedicalRecord->location_id,
                    'billing_provider_id' => $importedMedicalRecord->billing_provider_id,
                    'practice_id'         => $importedMedicalRecord->practice_id,
                ]);
        }

        if (optional($mr->providers)->isNotEmpty()) {
            ProviderLog::whereIn('id', $mr->providers->pluck('id')->all())
                ->update([
                    'location_id'         => $importedMedicalRecord->location_id,
                    'billing_provider_id' => $importedMedicalRecord->billing_provider_id,
                    'practice_id'         => $importedMedicalRecord->practice_id,
                ]);
        }

        $demographicsLogs = optional($mr->demographics)->first();

        if ($demographicsLogs) {
            if ( ! $demographicsLogs->mrn_number) {
                $demographicsLogs->mrn_number = "clh#{$mr->id}";
                $demographicsLogs->save();
            }
        }

        $importedMedicalRecord->save();
    }
}
