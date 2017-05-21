<?php

namespace App\Jobs;

use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Practice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
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
            $row['dob'] = Carbon::parse($row['dob'])->format('Y-m-d');
            $row['practice_id'] = $this->practice->id;

            if (array_key_exists('consent_date', $row)) {
                $row['consent_date'] = Carbon::parse($row['consent_date'])->format('Y-m-d');
            }

            $mr = TabularMedicalRecord::create($row);

            $importedMedicalRecords[] = $mr->import();
        }

        //gather the features for review
//        $document = null;
//        $providers = [];
//
//        $predictedLocationId = null;
//        $predictedPracticeId = null;
//        $predictedBillingProviderId = null;
//        $this->practicesCollection = Practice::with('locations.providers')
//            ->get([
//                'id',
//                'display_name',
//            ]);
//
//        //fixing up the data for vue. basically keying locations and providers by id
//        $this->practices = $this->practicesCollection->keyBy('id')
//            ->map(function ($this->practice) {
//                return [
//                    'id'           => $this->practice->id,
//                    'display_name' => $this->practice->display_name,
//                    'locations'    => $this->practice->locations->map(function ($loc) {
//                        //is there no better way to do this?
//                        $loc = new Collection($loc);
//
//                        $loc['providers'] = collect($loc['providers'])->keyBy('id');
//
//                        return $loc;
//                    })
//                        ->keyBy('id'),
//                ];
//            });
//
//        \JavaScript::put([
//            'predictedLocationId'        => $predictedLocationId,
//            'predictedPracticeId'        => $predictedPracticeId,
//            'predictedBillingProviderId' => $predictedBillingProviderId,
//            'practices'                  => $this->practices,
//        ]);
//
//        return view('importer.show-training-findings', compact([
//            'document',
//            'providers',
//            'importedMedicalRecords',
//        ]));
    }
}
