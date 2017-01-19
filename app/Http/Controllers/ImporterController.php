<?php namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class ImporterController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Receives XML files, saves them in DB, and returns them JSON Encoded
     *
     * @param Request $request
     *
     * @return string
     * @throws \Exception
     */
    public function uploadRawFiles(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json('No file found', 400);
        }

        foreach ($request->file('file') as $file) {
            $xml = file_get_contents($file);

            $json = $this->repo->toJson($xml);

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'vendor_id' => 1,
                'xml'       => $xml,
                'json'      => $json,
                'source'    => Ccda::IMPORTER,
            ]);

            $ccda->import();
        }

        return redirect()->route('view.files.ready.to.import');
    }

    /**
     * Show the form to upload CCDs.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('CCDUploader.uploader');
    }

    /**
     * Show all QASummaries that are related to a CCDA
     *
     * @painpoints:
     * 1. What about summaries not related to a CCDA? (Probably just delete them)
     * 2. Not sure if this should be in this Controller
     */
    public function index()
    {
        //get rid of orphans
        $delete = ImportedMedicalRecord::whereNull('medical_record_id')->delete();

        $qaSummaries = ImportedMedicalRecord::
        with('demographics')
            ->get()
            ->all();

        JavaScript::put([
            'importedMedicalRecords' => array_values($qaSummaries),
        ]);

        return view('CCDUploader.uploadedSummary');
    }

    //Train the Importing Algo
    public function train(Request $request)
    {
        if (!$request->hasFile('ccda')) {
            return 'Please upload a CCDA';
        }

        $xml = file_get_contents($request->file('ccda'));

        $json = $this->repo->toJson($xml);

        $ccda = Ccda::create([
            'user_id'   => auth()->user()->id,
            'vendor_id' => 1,
            'xml'       => $xml,
            'json'      => $json,
            'source'    => Ccda::IMPORTER,
        ]);

        $importedMedicalRecord = $ccda->import();

        //gather the features for review
        $document = $ccda->document;
        $providers = $ccda->providers;
        $predictedLocationId = $importedMedicalRecord->location_id;
        $predictedPracticeId = $importedMedicalRecord->practice_id;
        $predictedBillingProviderId = $importedMedicalRecord->billing_provider_id;
        $practicesCollection = Practice::with('locations.providers')
            ->get([
                'id',
                'display_name',
            ]);

        //fixing up the data for vue. basically keying locations and providers by id
        $practices = $practicesCollection->keyBy('id')
            ->map(function ($practice) {
                return [
                    'id'           => $practice->id,
                    'display_name' => $practice->display_name,
                    'locations'    => $practice->locations->map(function ($loc) {
                        //is there no better way to do this?
                        $loc = new Collection($loc);

                        $loc['providers'] = collect($loc['providers'])->keyBy('id');

                        return $loc;
                    })
                        ->keyBy('id'),
                ];
            });

        \JavaScript::put([
            'predictedLocationId'        => $predictedLocationId,
            'predictedPracticeId'        => $predictedPracticeId,
            'predictedBillingProviderId' => $predictedBillingProviderId,
            'practices'                  => $practices,
        ]);

        return view('importer.show-training-findings', compact([
            'document',
            'providers',
            'importedMedicalRecord',
        ]));
    }

    public function storeTrainingFeatures(Request $request)
    {
        if ($request->has('documentId')) {
            DocumentLog::whereId($request->input('documentId'))
                ->update([
                    'ml_ignore' => true,
                ]);
        }

        if ($request->has('providerIds')) {
            ProviderLog::whereIn('id', $request->input('providerIds'))
                ->update([
                    'ml_ignore' => true,
                ]);
        }

        $imr = ImportedMedicalRecord::find($request->input('imported_medical_record_id'));
        $imr->practice_id = $request->input('practice_id');
        $imr->location_id = $request->input('location_id');
        $imr->billing_provider_id = $request->input('billing_provider_id');
        $imr->save();

        return 'Thanks for training me!';
    }

}
