<?php namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Jobs\ImportCsvPatientList;
use App\Jobs\TrainCcdaImporter;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Practice;
use Carbon\Carbon;
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
            \Log::info('Begin processing CCD ' . Carbon::now()->toDateTimeString());
            $xml = file_get_contents($file);

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'vendor_id' => 1,
                'xml'       => $xml,
                'source'    => Ccda::IMPORTER,
            ]);

            $ccda->import();
            \Log::info('End processing CCD ' . Carbon::now()->toDateTimeString());
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
     */
    public function index()
    {
        //get rid of orphans
        $delete = ImportedMedicalRecord::whereNull('medical_record_id')->delete();

        $qaSummaries = ImportedMedicalRecord::whereNull('patient_id')
            ->with('demographics')
            ->with('practice')
            ->with('location')
            ->with('billingProvider')
            ->get()
            ->map(function ($summary) {
                $summary['flag'] = false;

                $providers = $summary->medicalRecord()->providers()->where([
                    ['first_name', '!=', null],
                    ['last_name', '!=', null],
                    ['ml_ignore', '=', false],
                ])->get();

                if ($providers->count() > 1) {
                    $summary['flag'] = true;
                }

                return $summary;
            })->values();

        JavaScript::put([
            'importedMedicalRecords' => $qaSummaries,
        ]);

        return view('CCDUploader.uploadedSummary');
    }

    public function getTrainingResults($imrId)
    {
        $importedMedicalRecord = ImportedMedicalRecord::find($imrId);

        if (!$importedMedicalRecord) {
            return "Could not find an Imported Medical Record with this ID";
        }

        $ccda = $importedMedicalRecord->medicalRecord();

        if (!$ccda) {
            return "Could not find the CCDA for this Imported Medical Record.";
        }
        //gather the features for review
        $document = $ccda->document->first();
        $providers = $ccda->providers()->where('ml_ignore', '=', false)->get();

        $predictedLocationId = $importedMedicalRecord->location_id;
        $predictedPracticeId = $importedMedicalRecord->practice_id;
        $predictedBillingProviderId = $importedMedicalRecord->billing_provider_id;

        return view('importer.show-training-findings', array_merge([
            'predictedBillingProviderId' => $predictedBillingProviderId,
            'predictedLocationId'        => $predictedLocationId,
            'predictedPracticeId'        => $predictedPracticeId,
            'medicalRecordId'            => $ccda->id
        ], compact([
            'document',
            'providers',
            'importedMedicalRecord',
        ])));
    }

    //Train the Importing Algo
    public function train(Request $request)
    {
        if (!$request->hasFile('medical_records')) {
            return 'Please upload a CCDA';
        }

        foreach ($request->allFiles()['medical_records'] as $file) {
            if ($file->getClientOriginalExtension() == 'csv') {
                dispatch((new ImportCsvPatientList(parseCsvToArray($file), $file->getClientOriginalName())));

                $link = link_to_route(
                    'view.files.ready.to.import',
                    'Visit to CCDs Ready to Import page to review imported files.'
                );

                return "The CSV list is being processed. $link";
            } //assume XML CCDA

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'vendor_id' => 1,
                'xml'       => file_get_contents($file),
                'source'    => Ccda::IMPORTER,
            ]);

            dispatch(new TrainCcdaImporter($ccda));
        }

        return redirect()->route('view.files.ready.to.import');
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

        $practiceId = $request->input('practiceId');
        $locationId = $request->input('locationId');
        $billingProviderId = $request->input('billingProviderId');

        $ids[] = $request->input('imported_medical_record_id');

        if ($request->has('imported_medical_record_ids')) {
            $ids = $request->input('imported_medical_record_ids');
        }

        foreach ($ids as $mrId) {
            $imr = ImportedMedicalRecord::find($mrId);
            $imr->practice_id = $practiceId;
            $imr->location_id = $locationId;
            $imr->billing_provider_id = $billingProviderId;
            $imr->save();


            //save the features on the medical record, document and provider logs
            $mr = app($imr->medical_record_type)->find($imr->medical_record_id);
            $mr->practice_id = $practiceId;
            $mr->location_id = $locationId;
            $mr->billing_provider_id = $billingProviderId;
            $mr->save();

            $docs = DocumentLog::where('medical_record_type', '=', $imr->medical_record_type)
                ->where('medical_record_id', '=', $imr->medical_record_id)
                ->update([
                    'practice_id'         => $practiceId,
                    'location_id'         => $locationId,
                    'billing_provider_id' => $billingProviderId,
                ]);

            $provs = ProviderLog::where('medical_record_type', '=', $imr->medical_record_type)
                ->where('medical_record_id', '=', $imr->medical_record_id)
                ->update([
                    'practice_id'         => $practiceId,
                    'location_id'         => $locationId,
                    'billing_provider_id' => $billingProviderId,
                ]);
        }

        return redirect()->route('view.files.ready.to.import');
    }
}
