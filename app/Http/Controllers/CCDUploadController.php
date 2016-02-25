<?php namespace App\Http\Controllers;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\QAImportSummary;
use App\CLH\CCD\Vendor\CcdVendor;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        //CCDs that already exist in XML_CCDs table
        $duplicates = [];

        //CCDs just added to XML_CCDs table
        $uploaded = [];

        if ( $request->hasFile( 'file' ) ) {

            foreach ( $request->file( 'file' ) as $file ) {
                if ( empty($file) ) {
                    Log::error( __METHOD__ . ' ' . __LINE__ . 'This CCDA did not upload. Here is what I have for CCDA ==>' . $file );
                    continue;
                }

                $xml = file_get_contents( $file );

                $json = $this->repo->toJson( $xml );

                $parsedCcd = json_decode( $json );

                if ( $request->session()->has( 'blogId' ) ) {
                    $blogId = $request->session()->get( 'blogId' );
                }
                else {
                    throw new \Exception( 'Blog id not found,', 400 );
                }

                $vendorId = empty($request->input( 'vendor' )) ?: $request->input( 'vendor' );

                $ccda = Ccda::create( [
                    'user_id' => 1,
                    'vendor_id' => $vendorId,
                    'xml' => $xml
                ] );
                $ccda->json = $json;
                $ccda->save();

                $importer = new QAImportManager( $blogId, $ccda );
                $output = $importer->generateCarePlanFromCCD();

                $jsonCcd = json_decode( $output->output, true );

                $name = function () use ($jsonCcd) {
                    return empty($name = $jsonCcd[ 'userMeta' ][ 'first_name' ] . ' ' . $jsonCcd[ 'userMeta' ][ 'last_name' ])
                        ?: $name;
                };

                $counter = function ($index) use ($jsonCcd) {
                    return count( explode( ';', $jsonCcd[ $index ] ) ) - 1;
                };

                $qaSummary = new QAImportSummary();
                $qaSummary->qa_output_id = $output->id;
                $qaSummary->name = $name();
                $qaSummary->medications = $counter( 3 );
                $qaSummary->problems = $counter( 1 );
                $qaSummary->allergies = $counter( 0 );
                $qaSummary->save();

                $qaSummaries[] = $qaSummary;
            }
        }

        return response()->json( $qaSummaries, 200 );
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
