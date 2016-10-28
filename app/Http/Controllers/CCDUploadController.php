<?php namespace App\Http\Controllers;

use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdVendor;
use App\Models\CCD\QAImportSummary;
use App\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class CCDUploadController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
        ini_set( 'memory_limit', '-1' );
    }

    /**
     * Receives XML files, saves them in DB, and returns them JSON Encoded
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function uploadRawFiles(Request $request)
    {
        if ( !$request->hasFile( 'file' ) ) {
            return response()->json( 'No file found', 400 );
        }

        $qaSummaries = new Collection();

        foreach ( $request->file( 'file' ) as $file ) {
            $xml = file_get_contents( $file );

            $json = $this->repo->toJson( $xml );

            $vendor = empty($request->input( 'ccd_vendor_id' )) ?: CcdVendor::find($request->input( 'ccd_vendor_id' ));

            $program = Practice::find($vendor->program_id);

            if (empty($program)) {
                throw new \Exception('Practice not found,', 400);
            }

            $ccda = Ccda::create( [
                'user_id'   => auth()->user()->id,
                'vendor_id' => $vendor->id,
                'xml'       => $xml,
                'json'      => $json,
                'source'    => Ccda::IMPORTER,
            ] );

            $logger = new CcdItemLogger( $ccda );
            $logger->logAll();

            $importer = new QAImportManager($program->id, $ccda);
            $output = $importer->generateCarePlanFromCCD();
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
        JavaScript::put( [
            'userBlogs' => auth()->user()->programs->keyBy('name')->sortBy('name'),
            'ccdVendors' => CcdVendor::all(),
        ] );

        return view( 'CCDUploader.uploader' );
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
        $delete = QAImportSummary::whereNull('ccda_id')->delete();

        $qaSummaries = QAImportSummary::with(['ccda' => function ($query) {
                $query->select('id', 'source', 'created_at')
                    ->whereNull('patient_id');
            }])
            ->get()
            ->sortBy('name')
            ->all();

        JavaScript::put( [
            'qaSummaries' => array_values($qaSummaries),
        ] );

        return view( 'CCDUploader.uploadedSummary' );
    }

}
