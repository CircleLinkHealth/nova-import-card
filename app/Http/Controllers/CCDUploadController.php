<?php namespace App\Http\Controllers;

use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\QAImportSummary;
use App\CLH\CCD\CcdVendor;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Location;
use Illuminate\Http\Request;
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

        foreach ( $request->file( 'file' ) as $file ) {
            $xml = file_get_contents( $file );

            $json = $this->repo->toJson( $xml );

            $blogId = $request->input( 'program_id' );

            if ( empty($blogId) ) throw new \Exception( 'Blog id not found,', 400 );

            $vendorId = empty($request->input( 'ccd_vendor_id' )) ?: $request->input( 'ccd_vendor_id' );

            $ccda = Ccda::create( [
                'user_id' => auth()->user()->ID,
                'vendor_id' => $vendorId,
                'xml' => $xml,
                'json' => $json,
                'source' => Ccda::IMPORTER,
            ] );

            $logger = new CcdItemLogger( $ccda );
            $logger->logAll();

            $importer = new QAImportManager( $blogId, $ccda );
            $output = $importer->generateCarePlanFromCCD();

            $qaSummaries[] = $output;
        }

        JavaScript::put( [
            'qaSummaries' => $qaSummaries,
        ] );

        return view( 'CCDUploader.uploadedSummary' );
    }

    /**
     * Show the form to upload CCDs.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        JavaScript::put( [
            'userBlogs' => auth()->user()->programs,
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
            ->toArray();

        JavaScript::put( [
            'qaSummaries' => $qaSummaries,
        ] );

        return view( 'CCDUploader.uploadedSummary' );
    }

}
