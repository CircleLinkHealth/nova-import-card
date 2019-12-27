<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Jobs\ImportCcda;
use App\Jobs\ImportCsvPatientList;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
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
     * Show the form to upload CCDs.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('saas.importer.create');
    }

    public function getImportedRecords()
    {
        return ImportedMedicalRecord::whereNull('patient_id')
            ->with('demographics')
            ->with('practice')
            ->with('location')
            ->with('billingProvider')
            ->get()
            ->transform(function (ImportedMedicalRecord $summary) {
                $summary['flag'] = false;

                $mr = $summary->medicalRecord();

                if ( ! $mr) {
                    return false;
                }

                if ( ! $summary->billing_provider_id) {
                    $mr = $mr->guessPracticeLocationProvider();

                    $summary->billing_provider_id = $mr->getBillingProviderId();

                    if ( ! $summary->location_id) {
                        $summary->location_id = $mr->getLocationId();
                    }

                    if ( ! $summary->practice_id) {
                        $summary->practice_id = $mr->getPracticeId();
                    }

                    if ($summary->isDirty()) {
                        $summary->save();
                    }
                }

                $providers = $mr->providers()->where([
                    ['first_name', '!=', null],
                    ['last_name', '!=', null],
                    ['ml_ignore', '=', false],
                ])->get()->unique(function ($m) {
                    return $m->first_name.$m->last_name;
                });

                if ($providers->count() > 1 || ! $mr->location_id || ! $mr->location_id || ! $mr->billing_provider_id) {
                    $summary['flag'] = true;
                }

                $summary->checkDuplicity();

                return $summary;
            })->filter()
            ->values();
    }

    public function getTrainingResults($imrId)
    {
        $importedMedicalRecord = ImportedMedicalRecord::find($imrId);

        if ( ! $importedMedicalRecord) {
            return 'Could not find an Imported Medical Record with this ID';
        }

        $ccda = $importedMedicalRecord->medicalRecord();

        if ( ! $ccda) {
            return 'Could not find the CCDA for this Imported Medical Record.';
        }
        //gather the features for review
        $document  = $ccda->document->first();
        $providers = $ccda->providers()->where('ml_ignore', '=', false)->get();

        $predictedLocationId        = $importedMedicalRecord->location_id;
        $predictedPracticeId        = $importedMedicalRecord->practice_id;
        $predictedBillingProviderId = $importedMedicalRecord->billing_provider_id;

        return view('importer.show-training-findings', array_merge([
            'predictedBillingProviderId' => $predictedBillingProviderId,
            'predictedLocationId'        => $predictedLocationId,
            'predictedPracticeId'        => $predictedPracticeId,
            'medicalRecordId'            => $ccda->id,
        ], compact([
            'document',
            'providers',
            'importedMedicalRecord',
        ])));
    }

    public function handleCcdFilesUpload(Request $request)
    {
        if ( ! $request->hasFile('file')) {
            return response()->json('No file found', 400);
        }

        $records = new Collection();

        foreach ($request->file('file') as $file) {
            \Log::info('Begin processing CCD '.Carbon::now()->toDateTimeString());
            $xml = file_get_contents($file);

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'vendor_id' => 1,
                'xml'       => $xml,
                'source'    => Ccda::IMPORTER,
            ]);

            $records->push($ccda->import());
            \Log::info('End processing CCD '.Carbon::now()->toDateTimeString());
        }

        return $records;
    }

    /**
     * Show all QASummaries that are related to a CCDA.
     */
    public function index()
    {
        //get rid of orphans
        $delete = ImportedMedicalRecord::whereNull('medical_record_id')->delete();

        $importedRecords = $this::getImportedRecords();

        JavaScript::put([
            'importedMedicalRecords' => $importedRecords,
        ]);

        return view('CCDUploader.uploadedSummary');
    }

    public function records()
    {
        return $this::getImportedRecords();
    }

    /**
     * Show the form to upload CCDs.
     *
     * @return \Illuminate\View\View
     */
    public function remix()
    {
        return view('CCDUploader.uploader-remix');
    }

    public function storeTrainingFeatures(Request $request)
    {
        if ($request->filled('documentId')) {
            DocumentLog::whereId($request->input('documentId'))
                ->update([
                    'ml_ignore' => true,
                ]);
        }

        if ($request->filled('providerIds')) {
            ProviderLog::whereIn('id', $request->input('providerIds'))
                ->update([
                    'ml_ignore' => true,
                ]);
        }

        $practiceId        = $request->input('practiceId');
        $locationId        = $request->input('locationId');
        $billingProviderId = $request->input('billingProviderId');

        $ids[] = $request->input('imported_medical_record_id');

        if ($request->filled('imported_medical_record_ids')) {
            $ids = $request->input('imported_medical_record_ids');
        }

        $records = new Collection();

        foreach ($ids as $mrId) {
            $imr                      = ImportedMedicalRecord::find($mrId);
            $imr->practice_id         = $practiceId;
            $imr->location_id         = $locationId;
            $imr->billing_provider_id = $billingProviderId;
            $imr->save();

            //save the features on the medical record, document and provider logs
            $mr                      = app($imr->medical_record_type)->find($imr->medical_record_id);
            $mr->practice_id         = $practiceId;
            $mr->location_id         = $locationId;
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

            $records->push($imr);
        }

        if ($request->has('json')) {
            return response()->json($records);
        }

        return redirect()->route('view.files.ready.to.import');
    }

    //Train the Importing Algo
    public function train(Request $request)
    {
        if ( ! $request->hasFile('medical_records')) {
            return 'Please upload a CCDA';
        }

        foreach ($request->allFiles()['medical_records'] as $file) {
            if ('csv' == $file->getClientOriginalExtension()) {
                ImportCsvPatientList::dispatch(
                    parseCsvToArray($file),
                    $file->getClientOriginalName()
                )->onQueue('low');

                $link = link_to_route(
                    'import.ccd.remix',
                    'Click here to view imported CCDs (refresh ...a lot).'
                );

                return "The CSV list is being processed. ${link}";
            } //assume XML CCDA

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'vendor_id' => 1,
                'xml'       => file_get_contents($file),
                'source'    => Ccda::IMPORTER,
            ]);

            ImportCcda::dispatch($ccda)->onQueue('low');
        }

        return redirect()->route('import.ccd.remix');
    }

    /**
     * Receives XML files, saves them in DB, and returns them JSON Encoded.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function uploadRawFiles(Request $request)
    {
        $this::handleCcdFilesUpload($request);

        return redirect()->route('view.files.ready.to.import');
    }

    /**
     * Route: /api/ccd-importer/import-medical-records.
     *
     * Receives XML and XLSX files, saves them in DB, and returns them JSON Encoded
     *
     * @throws \Exception
     *
     * @return string
     */
    public function uploadRecords(Request $request)
    {
        $records = $this::handleCcdFilesUpload($request);

        if ( ! $request->has('json')) {
            return redirect()->route('import.ccd.remix');
        }

        return response()->json($records);
    }
}
