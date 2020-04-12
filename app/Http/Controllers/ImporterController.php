<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Jobs\ImportCcda;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Http\Request;

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
        return Ccda::whereHas('patient.carePlan', function ($q) {
            $q->whereNull('status')->orWhere('status', CarePlan::DRAFT);
        })
                                    ->with(
                                        [
                                            'patient.billingProvider.user' => function ($q) {
                                                $q->select(
                                                    [
                                                        'users.id',
                                                        'saas_account_id',
                                                        'program_id',
                                                        'display_name',
                                                        'first_name',
                                                        'last_name',
                                                        'suffix',
                                                    ]
                                                );
                                            },
                                            'patient.patientNurseAsPatient.permanentNurse'       => function ($q) {
                                                $q->select(
                                                    [
                                                        'users.id',
                                                        'saas_account_id',
                                                        'program_id',
                                                        'display_name',
                                                        'first_name',
                                                        'last_name',
                                                        'suffix',
                                                    ]
                                                );
                                            },
                                            'patient.locations'        => function ($q) {
                                                $q->select(
                                                    [
                                                        'locations.id',
                                                        'practice_id',
                                                        'is_primary',
                                                        'name',
                                                    ]
                                                );
                                            },
                                            'patient.patientInfo',
                                            'patient.primaryPractice',
                                        ]
                                    )
                                    ->where(function ($q) {
                                        $q->whereIn('practice_id', auth()->user()->viewableProgramIds())
                                            ->when(auth()->user()->isAdmin(), function ($q) {
                                                $q->orWhereNull('practice_id');
                                            });
                                    })
                                    ->get()
                                    ->transform(
                function (Ccda $ccda) {
                    if (upg0506IsEnabled()) {
                        $isUpg0506Incomplete = false;
                        
                            $isUpg0506Incomplete = Ccda::whereHas(
                                'media',
                                function ($q) {
                                    $q->where('custom_properties->is_upg0506_complete', '!=', 'true');
                                }
                            )->whereHas(
                                'directMessage',
                                function ($q) {
                                    $q->where('from', 'like', '%@upg.ssdirect.aprima.com');
                                }
                            )->where('id', $ccda->id)->exists();
                        
                        if ($isUpg0506Incomplete) {
                            return false;
                        }
                    }
                    
                    $ccda->checkDuplicity();
                    
                    return $ccda;
                }
            )->filter()->unique('patient_id')
                                    ->values();
    }
    
    public function handleCcdFilesUpload(Request $request)
    {
        ini_set('upload_max_filesize', '50M');
        ini_set('post_max_size', '50M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);
        
        if ( ! $request->hasFile('file')) {
            return response()->json('No file found', 400);
        }
        
        //example: http://cpm.clh.test/ccd-importer?source=importer_awv
        $source = $this->getSource($request);
        
        $ccdas = [];
        foreach ($request->file('file') as $file) {
            \Log::warning("reading file $file");
            
            $xml = file_get_contents($file);
            
            \Log::info("finished reading file $file");
            
            $ccda = Ccda::create(
                [
                    'user_id' => auth()->user()->id,
                    'xml'     => $xml,
                    'source'  => $source ?? Ccda::IMPORTER,
                ]
            );
            
            ImportCcda::dispatch($ccda, true);
            $ccdas[] = $ccda->id;
        }
        
        return $ccdas;
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
    public function remix(Request $request)
    {
        return view('CCDUploader.uploader-remix')->with('shouldUseNewVersion', true);
    }
    
    /**
     * Receives XML files, saves them in DB, and returns them JSON Encoded.
     *
     * @return string
     * @throws \Exception
     *
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
     * @return string
     * @throws \Exception
     *
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
