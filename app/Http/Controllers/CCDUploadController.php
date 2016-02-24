<?php namespace App\Http\Controllers;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\Parser\CCDParser;
use App\CLH\CCD\QAImportSummary;
use App\CLH\CCD\Vendor\CcdVendor;
use App\CLH\Repositories\CCDImporterRepository;
use App\CLH\Repositories\WpUserRepository;
use App\Http\Requests;
use App\ParsedCCD;
use App\WpUser;
use App\XmlCCD;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDUploadController extends Controller
{

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
        ini_set('memory_limit','-1');
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

                $ccda = Ccda::create([
                    'user_id' => 1,
                    'vendor_id' => $vendorId,
                    'xml' => $xml
                ]);
                $ccda->json = $json;
                $ccda->save();

//                $user = $this->repo->createRandomUser( $blogId, '', $parsedCcd->demographics->name->given[0] );

                $importer = new QAImportManager( $blogId, $ccda );
                $output = $importer->generateCarePlanFromCCD();

                $jsonCcd = json_decode($output->output, true);

                $hasName = function () use ($jsonCcd) {
                    return ! empty($jsonCcd['userMeta']['first_name'] . $jsonCcd['userMeta']['last_name']);
                };

                $medications = function () use ($jsonCcd) {
                    return count(explode(';', $jsonCcd[3]));
                };

                $problems = function () use ($jsonCcd) {
                    return count(explode(';', $jsonCcd[1]));
                };

                $allergies = function () use ($jsonCcd) {
                    return count(explode(';', $jsonCcd[0]));
                };

                $qaSummary = new QAImportSummary();
                $qaSummary->qa_output_id = $output->id;
                $qaSummary->hasName = $hasName();
                $qaSummary->medications = $medications();
                $qaSummary->problems = $problems();
                $qaSummary->save();

                return response()->json( compact( 'qaSummary' ), 200 );

            }
        }

        return response()->json( compact( 'uploaded', 'duplicates' ), 200 );
    }

    public function uploadDuplicateRawFiles(Request $request)
    {
        $uploaded = [];

        $receivedFiles = json_decode( $request->getContent() );

        if ( empty($receivedFiles) ) return;

        foreach ( $receivedFiles as $file ) {

            if ( empty($file) ) {
                Log::error( 'It seems like this file did not upload. Here is what I have for $file in '
                    . self::class . '@uploadDuplicateRawFiles() ==>' . $file );
                continue;
            }

            $parser = new CCDParser( $file->xml );
            $fullName = $parser->getFullName();
            $email = empty($parser->getEmail())
                ? ''
                : $parser->getEmail();

            $user = $this->repo->createRandomUser( $file->blogId, $email, $fullName );

            $newCCD = new XmlCCD();
            $newCCD->ccd = $file->xml;
            $newCCD->user_id = $user->ID;
            $newCCD->patient_name = (string)$file->fullName;
            $newCCD->patient_dob = (string)$file->dob;
            $newCCD->save();

            array_push( $uploaded, [
                'userId' => $user->ID,
                'xml' => $file->xml,
                'vendor' => $file->vendor
            ] );
        }

        return response()->json( compact( 'uploaded' ), 200 );
    }

    /**
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $ccdVendors = CcdVendor::all();

        return view( 'CCDUploader.uploader', compact( 'ccdVendors' ) );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function storeParsedFiles(Request $request)
    {
        $receivedFiles = json_decode( $request->getContent() );

        if ( empty($receivedFiles) ) return;

        foreach ( $receivedFiles as $file ) {
            $parsedCCD = new ParsedCCD();
            $parsedCCD->ccd = json_encode( $file->ccd );
            $parsedCCD->user_id = $file->userId;
            $parsedCCD->save();

            if ( $request->session()->has( 'blogId' ) ) {
                $blogId = $request->session()->get( 'blogId' );
            }
            else {
                throw new \Exception( 'Blog ID missing.', 400 );
            }


            /**
             * The QAImportManager calls any necessary Parsers
             */
            $importer = new QAImportManager( $blogId, $parsedCCD, $parsedCCD->user_id, $file->vendor );
            $importer->generateCarePlanFromCCD();
        }

        return response()->json( 'Files received and processed successfully', 200 );
    }

}
