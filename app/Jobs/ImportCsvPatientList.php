<?php

namespace App\Jobs;

use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maknz\Slack\Facades\Slack;

class ImportCsvPatientList implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
     * @return void
     */
    public function __construct(array $file, $filename)
    {
        $this->patientsArr = $file;

        $this->practice = Practice::whereDisplayName(explode('-', $filename)[0])->first();

        if (!$this->practice) {
            dd('Please include the Practice name (as it appears on CPM) in the beginning of the csv filename as such. Demo name - Import List.');
        }
    }

    /**
     * Execute the job.
     *
     * @param ProcessEligibilityService $processEligibilityService
     *
     * @return void
     */
    public function handle(ProcessEligibilityService $processEligibilityService)
    {
        foreach ($this->patientsArr as $row) {
            if (isset($row['medical_record_type']) && isset($row['medical_record_id'])) {
                if ($processEligibilityService->isCcda($row['medical_record_type'])) {
                    $response = $processEligibilityService->importExistingCcda($row['medical_record_id']);

                    if ($response->success) {
                        $this->replaceWithValuesFromCsv($response->imr, $row);
                    }
                    continue;
                }
            }

            if (isset($row['patient_name'])) {
                $names = explode(', ', $row['patient_name']);
                $row['first_name'] = $names[0];
                $row['last_name'] = $names[1];
            }

            $this->createTabularMedicalRecordAndImport($row);
        }

        $url = url('view.files.ready.to.import');

        sendSlackMessage('#background-tasks', "Queued job Import CSV for {$this->practice->display_name} completed! Visit $url.");
    }



    /**
     * Get the most updated information from the csv (phone numbers, preferred call days/times, provider and so on).
     *
     * @param ImportedMedicalRecord $importedMedicalRecord
     * @param array $row
     */
    public function replaceWithValuesFromCsv(ImportedMedicalRecord $importedMedicalRecord, array $row)
    {
        $demographics = $importedMedicalRecord->demographics;

        $demographics->primary_phone = $row['primary_phone'] ?? '';
        $demographics->preferred_call_times = $row['preferred_call_times'] ?? '';
        $demographics->preferred_call_days = $row['preferred_call_days'] ?? '';

        foreach (['cell_phone', 'home_phone', 'work_phone'] as $phone) {
            if (!array_key_exists($phone, $row)) {
                continue;
            }

            if ($demographics->{$phone} == $row[$phone]) {
                continue;
            }

            $demographics->{$phone} = $row[$phone];
        }

        $demographics->save();

        if (!$importedMedicalRecord->practice_id) {
            $importedMedicalRecord->practice_id = $this->practice->id;
        }

        if (!$importedMedicalRecord->location_id) {
            $importedMedicalRecord->location_id = $this->practice->primary_location_id;
        }

        if (!$importedMedicalRecord->billing_provider_id && array_key_exists('provider', $row)) {
            $providerName = explode(' ', $row['provider']);

            if (count($providerName) >= 2) {
                $provider = User::whereFirstName($providerName[0])
                    ->whereLastName($providerName[1])
                    ->first();
            }

            if (!empty($provider)) {
                $importedMedicalRecord->billing_provider_id = $provider->id;

                if ($provider->locations->first()) {
                    $importedMedicalRecord->location_id = $provider->locations->first()->id;
                }
            }
        }

        $mr = $importedMedicalRecord->medicalRecord();

        DocumentLog::whereIn('id', $mr->document->pluck('id')->all())
            ->update([
                'location_id'         => $importedMedicalRecord->location_id,
                'billing_provider_id' => $importedMedicalRecord->billing_provider_id,
                'practice_id'         => $importedMedicalRecord->practice_id,
            ]);

        ProviderLog::whereIn('id', $mr->providers->pluck('id')->all())
            ->update([
                'location_id'         => $importedMedicalRecord->location_id,
                'billing_provider_id' => $importedMedicalRecord->billing_provider_id,
                'practice_id'         => $importedMedicalRecord->practice_id,
            ]);

        $demographicsLogs = $mr->demographics->first();

        if ($demographicsLogs) {
            if (!$demographicsLogs->mrn_number) {
                $demographicsLogs->mrn_number = "clh#$mr->id";
                $demographicsLogs->save();
            }
        }

        $importedMedicalRecord->save();
    }

    /**
     * Create a TabularMedicalRecord for each row, and import it.
     *
     * @param $row
     *
     * @return bool|null
     */
    public function createTabularMedicalRecordAndImport($row)
    {
        $row['dob'] = $row['dob']
            ? Carbon::parse($row['dob'])->toDateString()
            : null;
        $row['practice_id'] = $this->practice->id;
        $row['location_id'] = $this->practice->primary_location_id;

        if (array_key_exists('consent_date', $row)) {
            $row['consent_date'] = Carbon::parse($row['consent_date'])->format('Y-m-d');
        }

        if (array_key_exists('street', $row)) {
            $row['address'] = $row['street'];
        }

        if (array_key_exists('street_2', $row)) {
            $row['address2'] = $row['street_2'];
        }

        if (array_key_exists('primary_phone', $row) && array_key_exists('primary_phone_type', $row)) {
            if (str_contains(strtolower($row['primary_phone_type']), ['cell', 'mobile'])) {
                $row['cell_phone'] = $row['primary_phone'];
            } elseif (str_contains(strtolower($row['primary_phone_type']), 'home')) {
                $row['home_phone'] = $row['primary_phone'];
            } elseif (str_contains(strtolower($row['primary_phone_type']), 'work')) {
                $row['work_phone'] = $row['primary_phone'];
            }
        }

        if (array_key_exists('alt_phone', $row) && array_key_exists('alt_phone_type', $row)) {
            if (str_contains(strtolower($row['alt_phone_type']), ['cell', 'mobile'])) {
                $row['cell_phone'] = $row['alt_phone'];
            } elseif (str_contains(strtolower($row['alt_phone_type']), 'home')) {
                $row['home_phone'] = $row['alt_phone'];
            } elseif (str_contains(strtolower($row['alt_phone_type']), 'work')) {
                $row['work_phone'] = $row['alt_phone'];
            }
        }

        $exists = TabularMedicalRecord::where([
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'dob' => $row['dob'],
        ])->first();

        if ($exists) {
            if ($exists->importedMedicalRecord()) {
                return null;
            }

            $exists->delete();
        }

        if ($this->practice->id == 139) {
            $mrn = $this->lookupPHXmrn($row['first_name'], $row['last_name'], $row['dob']);

            if (!$mrn) {
                return false;
            }

            $row['mrn'] = $mrn;
        }

        $mr = TabularMedicalRecord::create($row);

        $importedMedicalRecords[] = $mr->import();
    }

    private function lookupPHXmrn($firstName, $lastName, $dob) {
        $dob = Carbon::parse($dob)->toDateString();

        $row = PhoenixHeartName::where('patient_first_name', $firstName)
                               ->where('patient_last_name', $lastName)
                               ->where('dob', $dob)
                               ->first();

        if ($row && $row->patient_id) {
            return $row->patient_id;
        }

        return null;
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        sendSlackMessage('#background-tasks', "Queued job Import CSV patient list failed: $exception");
    }
}
