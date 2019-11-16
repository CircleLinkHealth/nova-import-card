<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 01/07/2019
 * Time: 1:06 PM
 */

namespace App\Http\Controllers;

use App\Filters\PatientListFilters;
use App\Http\Requests\StorePatientRequest;
use App\Patient;
use App\PatientAwvSurveyInstanceStatusView;
use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('patientList');
    }

    /**
     * Create a patient manually, while creating a provider, if needed
     *
     * @param StorePatientRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePatientRequest $request)
    {
        $providerUserId = $this->getPatientProvider($request);
        $patientUserId  = $this->createPatient($request);

        CarePerson::updateOrCreate([
            'user_id'        => $patientUserId,
            'member_user_id' => $providerUserId,
        ], [
            'alert' => false,
            'type'  => CarePerson::BILLING_PROVIDER,
        ]);

        return response()->json([
            'user_id' => $patientUserId,
        ]);
    }

    /**
     * Gets or creates a provider.
     *
     * @param StorePatientRequest $request
     *
     * @return int the user id of the provider
     */
    private function getPatientProvider(StorePatientRequest $request): int
    {
        $providerInput = $request->input('provider');

        if ( ! empty($providerInput['id'])) {
            $providerUserId = $providerInput['id'];
        } else {
            $isClinical = $providerInput['suffix'] === 'non-clinical';
            //need to create the $providerUser
            $providerUser = new User([
                'suffix'               => $isClinical
                    ? null
                    : $providerInput['suffix'],
                'program_id'           => $providerInput['primaryPracticeId'],
                'auto_attach_programs' => 0,
                'address'              => '',
                'address2'             => '',
                'city'                 => '',
                'state'                => '',
                'zip'                  => '',
            ]);

            $providerUser->status          = 'Active';
            $providerUser->access_disabled = 1;
            $providerUser->setFirstName($providerInput['firstName']);
            $providerUser->setLastName($providerInput['lastName']);
            $providerUser->createNewUser($providerInput['email'], str_random());

            ProviderInfo::updateOrCreate([
                'user_id' => $providerUser->id,
            ], [
                'is_clinical' => $isClinical,
                'specialty'   => $providerInput['specialty'],
            ]);

            if (isset($providerInput['phoneNumber'])) {
                $providerUser->clearAllPhonesAndAddNewPrimary($providerInput['phoneNumber'], null, true);
            }

            $providerUserId = $providerUser->id;
        }

        return $providerUserId;
    }

    /**
     * Create an AWV patient.
     *
     * @param StorePatientRequest $request
     *
     * @return int|mixed
     */
    private function createPatient(StorePatientRequest $request)
    {
        $patientInput = $request->input('patient');

        //BASIC
        if (empty($patientInput['email'])) {
            $patientInput['email'] = $this->getRandomEmail();
        }

        $user = new User([
            'email'                => $patientInput['email'],
            'user_registered'      => date('Y-m-d H:i:s'),
            'auto_attach_programs' => 0,
            'address'              => '',
            'address2'             => '',
            'city'                 => '',
            'state'                => '',
            'zip'                  => '',
        ]);

        $user->status          = 'Active';
        $user->access_disabled = 1;
        $user->setFirstName($patientInput['firstName']);
        $user->setLastName($patientInput['lastName']);
        $user->createNewUser($patientInput['email'], str_random());
        $user->clearAllPhonesAndAddNewPrimary($patientInput['phoneNumber'], null, true);

        //ROLES
        $providerUser = $request->input('provider');
        $roles        = Role::getIdsFromNames(['participant']);
        $user->attachRoleForPractice($roles, $providerUser['primaryPracticeId']);

        //PATIENT INFO
        Patient::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'ccm_status' => Patient::NA,
            'birth_date' => Carbon::parse($patientInput['dob']),
            'is_awv'     => true,
        ]);

        return $user->id;
    }

    /**
     * Generate a random email. Needed when email for user is not supplied.
     *
     * @return string
     */
    private function getRandomEmail()
    {
        return 'awv_' . str_random(20) . '@careplanmanager.com';
    }

    public function getPatientList(Request $request, PatientListFilters $filters)
    {
        $fields = ['*'];

        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $data = PatientAwvSurveyInstanceStatusView::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
             ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];
    }

    public function getPatientContactInfo(Request $request, $userId)
    {
        $user = User::with([
            'phoneNumbers',
        ])->findOrFail($userId);

        $filteredPhoneNumbers = $user->phoneNumbers->filter(function ($phone) {
            return ! empty(trim($phone->number));
        });

        return response()->json([
            'user_id'       => $userId,
            'phone_numbers' => $filteredPhoneNumbers,
            'email'         => $user->email,
        ]);
    }

    public function getPatientReport($patienId, $reportType, $year)
    {

        if ($reportType == 'ppp') {
            return redirect()->route('get-ppp-report', [
                'userId' => $patienId,
                'year'   => $year,

            ]);
        }

        if ($reportType == 'provider-report') {
            return redirect()->route('get-provider-report', [
                'userId' => $patienId,
                'year'   => $year,

            ]);
        }

        throw new \Exception("Report type : [$reportType] does not exist.");

    }
}
