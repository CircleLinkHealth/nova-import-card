<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Jobs\ImportCcda;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Http\Request;
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

                $summary['flag'] = false;
                
                if ($providers->count() > 1 || ! $mr->location_id || ! $mr->location_id || ! $mr->billing_provider_id) {
                    $summary['flag'] = true;
                }

                $summary->checkDuplicity();

                return $summary;
            })->filter()
            ->values();
    }

    public function handleCcdFilesUpload(Request $request)
    {
        if ( ! $request->hasFile('file')) {
            return response()->json('No file found', 400);
        }

        //example: http://cpm.clh.test/ccd-importer?source=importer_awv
        $source = $this->getSource($request);

        $ccdas = [];
        foreach ($request->file('file') as $file) {
            \Log::channel('logdna')->warning("reading file $file");
    
            $xml = file_get_contents($file);

            $ccda = Ccda::create([
                'user_id'   => auth()->user()->id,
                'xml'       => $xml,
                'source'    => $source ?? Ccda::IMPORTER,
            ]);

            ImportCcda::dispatch($ccda, true);
            $ccdas[] = $ccda->id;
        }
        return $ccdas;
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
        $ccdas = $this::handleCcdFilesUpload($request);

        if ( ! $request->has('json')) {
            return redirect()->route('import.ccd.remix');
        }

        return response()->json(['ccdas' => $ccdas]);
    }

    /**
     * The source of the importer can be submitted through
     * the url. In this case we are checking if available through
     * HTTP_REFERRER (previous url)
     * i.e http://cpm.clh.test/ccd-importer?source=importer_awv.
     *
     * @return mixed|null
     */
    private function getSource(Request $request)
    {
        if ($request->has('source')) {
            return $request->input('source');
        }

        if (empty($_SERVER['HTTP_REFERER'])) {
            return null;
        }

        $url   = $_SERVER['HTTP_REFERER'];
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            return null;
        }

        parse_str($parts['query'], $query);

        return $query['source'] ?? null;
    }
}
