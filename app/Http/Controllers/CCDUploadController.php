<?php namespace App\Http\Controllers;

use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\Vendor\CcdVendor;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JavaScript;

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
        if ( $request->hasFile( 'file' ) ) {
            foreach ( $request->file( 'file' ) as $file ) {
                if ( empty($file) ) {
                    Log::error( __METHOD__ . ' ' . __LINE__ . 'This CCDA did not upload. Here is what I have for CCDA ==>' . $file );
                    continue;
                }

                $xml = file_get_contents( $file );

                $json = $this->repo->toJson( $xml );

                $blogId = $request->input( 'program_id' );

                if ( empty($blogId) ) throw new \Exception( 'Blog id not found,', 400 );

                $vendorId = empty($request->input( 'ccd_vendor_id' )) ?: $request->input( 'ccd_vendor_id' );

                $ccda = Ccda::create( [
                    'user_id' => auth()->user()->ID,
                    'vendor_id' => $vendorId,
                    'xml' => $xml,
                    'json' => $json
                ] );

                $logger = new CcdItemLogger( $ccda );
                $logger->logAll();

                $importer = new QAImportManager( $blogId, $ccda );
                $output = $importer->generateCarePlanFromCCD();

                $qaSummaries[] = $output;
            }
        }

        $locations = Location::whereNotNull( 'parent_id' )->get();

//        return response()->json( compact( 'qaSummaries', 'locations' ), 200 );
        JavaScript::put( [
            'qaSummaries' => $qaSummaries,
            'locations' => $locations,
        ] );

        return view( 'CCDUploader.uploadedSummary' );
    }

    /**
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

}
