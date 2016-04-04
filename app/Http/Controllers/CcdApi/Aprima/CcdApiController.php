<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\Activity;
use App\CcmTimeApiLog;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\Importer\QAImportManager;
use App\CLH\CCD\ItemLogger\CcdItemLogger;
use App\CLH\Contracts\Repositories\UserRepository;
use App\CLH\Repositories\CCDImporterRepository;
use App\ForeignId;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CLH\CCD\ValidatesQAImportOutput;

use App\PatientReports;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CcdApiController extends Controller
{

    use ValidatesQAImportOutput;

    private $repo;
    private $users;

    public function __construct(CCDImporterRepository $repo, UserRepository $users)
    {
        $this->repo = $repo;
        $this->users = $users;
    }

    public function getCcmTime(Request $request)
    {
        if ( !\Session::has( 'apiUser' ) ) {
            return response()->json( ['error' => 'Authentication failed.'], 403 );
        }

        $user = \Session::get( 'apiUser' );

        //If there is no start date, set a really old date to include all activities
        $startDate = empty($from = $request->input( 'start_date' ))
            ? Carbon::createFromDate( '1990', '01', '01' )
            : Carbon::parse( $from )->startOfDay();

        //If there is no end date, set tomorrow's date to include all activities
        $endDate = empty($to = $request->input( 'end_date' ))
            ? Carbon::tomorrow()
            : Carbon::parse( $to )->endOfDay();

        $sendAll = $request->input( 'send_all' )
            ? true
            : false;

        $locationId = $this->getApiUserLocation( $user );

        //Dynamically get all the tables' names since we'll probably change them soon
        $activitiesTable = ( new Activity() )->getTable();
        $ccdaTable = ( new Ccda() )->getTable();
        $ccmApiLogTable = ( new CcmTimeApiLog() )->getTable();
        $patientTable = ( new DemographicsImport() )->getTable();
        $foreignIdTable = ( new ForeignId() )->getTable();
        $userTable = ( new User() )->getTable();

        $patientAndProviderIds = DemographicsImport::select( DB::raw( "$patientTable.mrn_number as patientId,
                $ccdaTable.patient_id as clhPatientUserId,
                $foreignIdTable.foreign_id as providerId,
                $patientTable.provider_id as clhProviderUserId"
        ) )
            ->join( $ccdaTable, "$ccdaTable.id", '=', "$patientTable.ccda_id" )
            ->whereNotNull( "$ccdaTable.patient_id" )
            ->join( $foreignIdTable, "$foreignIdTable.user_id", '=', "$patientTable.provider_id" )
            ->where( "$foreignIdTable.system", '=', ForeignId::APRIMA )
            ->whereNotNull( "$foreignIdTable.foreign_id" )
            ->whereLocationId( $locationId )
            ->get();

        foreach ( $patientAndProviderIds as $ids ) {
            $activities = Activity::select( DB::raw( "
                $activitiesTable.id as id,
                type as commentString,
                duration as length,
                duration_unit as lengthUnit,
                $userTable.display_name as servicePerson,
                $activitiesTable.performed_at as startingDateTime
                " ) )
                ->whereProviderId( $ids->clhProviderUserId )
                ->wherePatientId( $ids->clhPatientUserId )
                ->join( $userTable, "$userTable.ID", '=', "$activitiesTable.provider_id" )
                ->whereBetween( "$activitiesTable.performed_at", [
                    $startDate, $endDate
                ] );
            if ( !$sendAll ) {
                $activities->whereNotIn( "$activitiesTable.id", CcmTimeApiLog::lists( 'activity_id' ) );
            }
            $activities = $activities->get();

            if ( $activities->isEmpty() ) continue;

            $careEvents = $activities->map( function ($careEvent) {

                CcmTimeApiLog::updateOrCreate( ['activity_id' => $careEvent->id] );

                return [
                    'servicePerson' => $careEvent->servicePerson,
                    'startingDateTime' => $careEvent->startingDateTime,
                    'length' => $careEvent->length,
                    'lengthUnit' => $careEvent->lengthUnit,
                    'commentString' => $careEvent->commentString,
                ];
            } );

            $results[] = [
                'patientId' => $ids->patientId,
                'providerId' => $ids->providerId,
                'careEvents' => $careEvents
            ];
        }

        return isset($results)
            ? response()->json( $results, 200 )
            : response()->json( ["message" => "No pending care events."], 404 );
    }

    public function reports(Request $request)
    {

        if ( !\Session::has( 'apiUser' ) ) {
            return response()->json( ['error' => 'Authentication failed.'], 403 );
        }

        $user = \Session::get( 'apiUser' );

        //If there is no start date, set a really old date to include all activities
        $startDate = empty($from = $request->input( 'start_date' ))
            ? Carbon::createFromDate( '1990', '01', '01' )
            : Carbon::parse( $from )->startOfDay();

        //If there is no end date, set tomorrow's date to include all activities
        $endDate = empty($to = $request->input( 'end_date' ))
            ? Carbon::tomorrow()
            : Carbon::parse( $to )->endOfDay();

        $sendAll = $request->input( 'send_all' )
            ? true
            : false;

        $locationId = $this->getApiUserLocation( $user );

        //$pendingReports = PatientReports::where('location_id',$locationId);
        $pendingReports = PatientReports::where( 'location_id', $locationId )
            ->whereBetween( 'created_at', [
                $startDate, $endDate
            ] );
        if ( $sendAll ) {
            $pendingReports->withTrashed();
        }

        $pendingReports = $pendingReports->get();

        if ( $pendingReports->isEmpty() ) {
            return response()->json( ["message" => "No Pending Reports"], 404 );
        }

        $json = array();
        $i = 0;
        foreach ( $pendingReports as $report ) {

            //Get patient's lead provider
            $patient_provider = User::find( $report->patient_id )->getLeadContactIDAttribute();
            if ( !$patient_provider ) {
                continue;
            }

            //Get lead provider's foreign_id
            $foreignId_obj = ForeignId::where( 'system', ForeignId::APRIMA )->where( 'user_id', $patient_provider )->first();

            //Check if field exists
            if ( !$report->file_base64 ) {
                continue;
            }

            if ( $foreignId_obj->foreign_id ) {
                $json[ $i ] = [
                    'patientId' => $report->patient_mrn,
                    'providerId' => $foreignId_obj->foreign_id,
                    'file' => $report->file_base64,
                    'fileType' => $report->file_type,
                    'created_at' => $report->created_at->toDateTimeString(),
                ];
            }
            $i++;
        }

        PatientReports::where( 'location_id', $locationId )->delete();

        return response()->json( $json, 200, ['fileCount' => count( $json )] );
    }

    public function uploadCcd(Request $request)
    {
        if ( !\Session::has( 'apiUser' ) ) {
            response()->json( ['error' => 'Authentication failed.'], 403 );
        }

        $user = \Session::get( 'apiUser' );

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
            'source' => Ccda::API,
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
        //Yes. Repetitions. I KNOW!
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
            'mantoniou@circlelinkhealth.com',
            'jkatz@circlelinkhealth.com',
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

    public function getApiUserLocation($user)
    {
        $apiUserLocation = $user->locations;

        try {
            $locationId = $apiUserLocation[ 0 ]->pivot->location_id;
        } catch ( \Exception $e ) {
            return response()->json( 'Could not resolve a Location from your User.', 400 );
        }

        return $locationId;
    }
}
