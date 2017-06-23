<?php

namespace App\Jobs;

use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
            if (in_array($row['mrn'], ['#N/A'])) {
                continue;
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
                    continue;
                }

                $exists->delete();
            }

            $mr = TabularMedicalRecord::create($row);

            $importedMedicalRecords[] = $mr->import();
        }

//        $url = url('view.files.ready.to.import');

//        Slack::to('#background-tasks')->send("Queued job Import CSV for {$this->practice->display_name} completed! Visit $url.");
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
//        Slack::to('#background-tasks')->send("Queued job Import CSV patient list failed: $exception");
    }
}
