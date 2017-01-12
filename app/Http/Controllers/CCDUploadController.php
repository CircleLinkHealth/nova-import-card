<?php namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use Illuminate\Http\Request;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class CCDUploadController extends Controller
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

}
