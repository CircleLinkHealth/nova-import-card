<?php

namespace App\Jobs;

use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Practice;
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
     * @return void
     */
    public function handle()
    {
        foreach ($this->patientsArr as $row) {
            if (isset($row['medical_record_type'])) {
                if ($row['medical_record_type'] == Ccda::class) {
                    $imr = $this->importExistingCcda($row['medical_record_id']);

                    if ($imr) {
                        $this->replaceWithValuesFromCsv($imr, $row);
                    }
                    continue;
                }
            }

            if (isset($row['mrn'])) {
                $this->createTabularMedicalRecordAndImport($row);
            }
        }

        $url = url('view.files.ready.to.import');

        sendSlackMessage('#background-tasks', "Queued job Import CSV for {$this->practice->display_name} completed! Visit $url.");
    }

    /**
     * Import a Patient whose CCDA we have already.
     *
     * @param $ccdaId
     *
     * @return ImportedMedicalRecord|bool
     */
    public function importExistingCcda($ccdaId)
    {
        $ccda = Ccda::where([
            'id'       => $ccdaId,
            'imported' => false,
        ])->first();

        if (!$ccda) {
            return false;
        }

        $imr = $ccda->import();

        $update = Ccda::whereId($ccdaId)
            ->update([
                'status'   => Ccda::QA,
                'imported' => true,
            ]);

        return $imr;
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

        $demographics->primary_phone = $row['primary_phone'];
        $demographics->preferred_call_times = $row['preferred_call_times'];
        $demographics->preferred_call_days = $row['preferred_call_days'];

        foreach (['cell_phone', 'home_phone', 'work_phone'] as $phone) {
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

        if (!$importedMedicalRecord->billing_provider_id) {
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
        if (in_array($row['mrn'], ['#N/A'])) {
            return false;
        }

        $row['dob'] = $row['dob']
            ? Carbon::parse($row['dob'])->format('Y-m-d')
            : null;
        $row['practice_id'] = $this->practice->id;
        $row['location_id'] = $this->practice->primary_location_id;

        if (array_key_exists('consent_date', $row)) {
            $row['consent_date'] = Carbon::parse($row['consent_date'])->format('Y-m-d');
        }

        $exists = TabularMedicalRecord::where([
            'mrn' => $row['mrn'],
            'dob' => $row['dob'],
        ])->first();

        if ($exists) {
            if ($exists->importedMedicalRecord()) {
                return null;
            }

            $exists->delete();
        }

        $mr = TabularMedicalRecord::create($row);

        $importedMedicalRecords[] = $mr->import();
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
