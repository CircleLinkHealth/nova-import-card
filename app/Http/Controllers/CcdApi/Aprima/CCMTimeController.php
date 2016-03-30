<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\Activity;
use App\CLH\CCD\Ccda;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\Contracts\Repositories\UserRepository;
use App\ForeignId;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use App\WpBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CCMTimeController extends Controller
{

    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function getCcmTime()
    {
        $demo = [
            [
                'patientId' => 103,
                'providerId' => 100,
                'careEvents' => [
                    [
                        'servicePerson' => 'Bob',
                        'startingDateTime' => '2015-05-26 18:32:00',
                        'length' => '10',
                        'commentstring' => 'Call Center Contact'
                    ],
                    [
                        'servicePerson' => 'Marie',
                        'startingDateTime' => '2015-05-26 18:12:00',
                        'length' => '8',
                        'commentstring' => 'Blood Pressure Monitor'
                    ]
                ]
            ],
            [
                'patientId' => 105,
                'providerId' => 101,
                'careEvents' => [
                    [
                        'servicePerson' => 'Mario',
                        'startingDateTime' => '2015-05-26 18:32:00',
                        'length' => '5',
                        'commentstring' => 'Measure Weigh'
                    ],
                    [
                        'servicePerson' => 'John',
                        'startingDateTime' => '2015-05-26 18:12:00',
                        'length' => '2',
                        'commentstring' => 'Call Center Contact'
                    ]
                ]
            ]
        ];

//		return json_encode($demo);

//        if ( !\Session::has( 'apiUser' ) ) {
//            response()->json( ['error' => 'Authentication failed.'], 403 );
//        }

//        $user = \Session::get( 'apiUser' );

        $user = User::find( 747 );

        $providerLocations = $user->locations;
        $locationId = $providerLocations[ 0 ]->pivot->location_id;

        $ccdaTable = ( new Ccda() )->getTable();
        $patientTable = ( new DemographicsImport() )->getTable();
        $foreignIdTable = ( new ForeignId() )->getTable();

        $results = DemographicsImport::select( DB::raw( "$patientTable.mrn_number as patientId,
                $ccdaTable.patient_id as clhPatientUserId,
                $foreignIdTable.foreign_id as providerId,
                $patientTable.provider_id as clhProviderUserId"
        ) )
            ->join( $ccdaTable, "$ccdaTable.id", '=', "$patientTable.ccda_id" )
            ->join( $foreignIdTable, "$foreignIdTable.user_id", '=', "$patientTable.provider_id" )
            ->where( "$foreignIdTable.system", '=', ForeignId::APRIMA )
            ->get();

        return $results;
    }


}
