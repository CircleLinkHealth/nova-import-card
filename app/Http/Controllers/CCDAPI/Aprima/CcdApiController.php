<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\CLH\CCD\Ccda;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Repositories\CCDImporterRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CLH\CCD\ValidatesQAImportOutput;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CcdApiController extends Controller
{

    use ValidatesQAImportOutput;

    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * This function will authenticate te user using their username and password and return an access token.
     *
     * @param Request $request
     * @return string access_token
     */
    public function getAccessToken(Request $request)
    {
        if ( !$request->has( 'username' ) || !$request->has( 'password' ) ) {
            response()->json( ['error' => 'Username and password need to be included on the request.'], 400 );
        }

        $credentials = [
            'email' => $request->input( 'username' ),
            'user_pass' => $request->input( 'password' ),
        ];

        \JWTAuth::setIdentifier( 'ID' );

        if ( !$access_token = \JWTAuth::attempt( $credentials ) ) {
            return response()->json( ['error' => 'Invalid Credentials.'], 400 );
        }

        return response()->json( compact( 'access_token' ), 200 );
    }


    public function uploadCcd(Request $request)
    {
        //adjust Aprima's request to match JWT Auth
        $user = $this->transformAndValidateRequest($request);

        if ( !$user ) {
            return response()->json( ['error' => 'Invalid Token'], 400 );
        }

        if ( !$user->can( 'post-ccd-to-api' ) ) {
            response()->json( ['error' => 'You are not authorized to submit CCDs to this API.'], 403 );
        }

        if ( !$request->has( 'file' ) ) {
            response()->json( ['error' => 'No file found on the request.'], 422 );
        }

        $programId = $user->blogId();

        try {
            $xml = base64_decode( $request->input( 'file' ) );
        } catch ( \Exception $e ) {
            return response()->json( ['error' => 'Failed to base64_decode CCD.'], 400 );
        }

        $ccdObj = Ccda::create( [
            'user_id' => $user->ID,
            'vendor_id' => 1,
            'xml' => $xml,
        ] );

        //We are saving the JSON CCD after we save the XML, just in case Parsing fails
        //If Parsing fails we let ourselves know, but not Aprima.
        try {
            $json = $this->repo->toJson( $xml );
            $ccdObj->json = $json;
            $ccdObj->save();
        } catch ( \Exception $e ) {
            if ( app()->environment( 'production' ) ) {
                $this->notifyAdmins( $user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage() );
            }
            return response()->json( ['message' => 'CCD uploaded successfully.'], 201 );
        }


        //If Logging fails we let ourselves know, but not Aprima.
        try {
            $logger = new CcdItemLogger( $ccdObj );
            $logger->logAll();
        } catch ( \Exception $e ) {
            if ( app()->environment( 'production' ) ) {
                $this->notifyAdmins( $user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage() );
            }
            return response()->json( ['message' => 'CCD uploaded successfully.'], 201 );
        }

        //If Logging fails we let ourselves know, but not Aprima.
        try {
            $importer = new QAImportManager( $programId, $ccdObj );
            $output = $importer->generateCarePlanFromCCD();
        } catch ( \Exception $e ) {
            if ( app()->environment( 'production' ) ) {
                $this->notifyAdmins( $user, $ccdObj, 'bad', __METHOD__ . ' ' . __LINE__, $e->getMessage() );
            }
            return response()->json( ['message' => 'CCD uploaded successfully.'], 201 );
        }

        if ( app()->environment( 'production' ) ) {
            $this->notifyAdmins( $user, $ccdObj, 'well' );
        }

        return response()->json( ['message' => 'CCD uploaded successfully.'], 201 );
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     *
     * @param User $user
     * @param Ccda $ccda
     * @param $status
     * @param null $line
     * @param null $errorMessage
     */
    public function notifyAdmins(User $user, Ccda $ccda, $status, $line = null, $errorMessage = null)
    {
        $recipients = [
            'Plawlor@circlelinkhealth.com',
            'rohanm@circlelinkhealth.com',
            'mantoniou@circlelinkhealth.com'
        ];

        $view = 'emails.aprimaSentCCDs';
        $subject = "Aprima sent a CCD. It went {$status}";

        $data = [
            'ccdId' => $ccda->id,
            'errorMessage' => $errorMessage,
            'userId' => $user->ID,
            'line' => $line,
        ];

        Mail::send( $view, $data, function ($message) use ($recipients, $subject) {
            $message->from( 'aprima-api@careplanmanager.com', 'CircleLink Health' );
            $message->to( $recipients )->subject( $subject );
        } );
    }


    /**
     * This will add an Authorization header to Aprima's request. We do this because we already gave
     * them documentation for the separate API we had, and we don't wanna have them change their stuff.
     *
     * @param Request $request
     * @return \App\User $user
     */
    public function transformAndValidateRequest(Request $request)
    {
        if ( !$request->has( 'access_token' ) ) {
            return response()->json( ['error' => 'Access token not found on the request.'], 400 );
        }

        //Adds Authorization Header to make Aprima's request match our JWT Auth
        $request->headers->set( 'Authorization', "Bearer {$request->input('access_token')}" );

        return \JWTAuth::parseToken()->authenticate();
    }

}
