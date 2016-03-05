<?php namespace App\Http\Controllers;

use App\CLH\CCD\ImportedItemsLogger\CcdItemLogger;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ValidatesQAImportOutput;
use App\CLH\CCD\Vendor\CcdVendor;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CCDUploadController extends Controller
{
    use ValidatesQAImportOutput;

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

                if ( !$request->session()->has( 'blogId' ) ) throw new \Exception( 'Blog id not found,', 400 );
                $blogId = $request->session()->get( 'blogId' );

                $vendorId = empty($request->input( 'vendor' )) ?: $request->input( 'vendor' );

                $ccda = Ccda::create( [
                    'user_id' => 1,
                    'vendor_id' => $vendorId,
                    'xml' => $xml,
                    'json' => $json
                ] );

                $logger = new CcdItemLogger($ccda);
                $logger->logAll();

                $importer = new QAImportManager( $blogId, $ccda );
                $output = $importer->generateCarePlanFromCCD();

                $qaSummaries[] = $this->validateQAImportOutput( $output );
            }
        }

        $locations = Location::whereNotNull( 'parent_id' );

        return response()->json( compact( 'qaSummaries', 'locations' ), 200 );
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $ccdVendors = CcdVendor::all();

        return view( 'CCDUploader.uploader', compact( 'ccdVendors' ) );
    }

}
