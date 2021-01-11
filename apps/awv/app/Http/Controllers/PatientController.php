<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Filters\PatientListFilters;
use App\Http\Requests\StorePatientRequest;
use App\Patient;
use App\PatientAwvSurveyInstanceStatusView;
use App\Services\SurveyInvitationLinksService;
use App\User;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
                ? 'asc'
                : 'desc';
            $data->orderBy($orderBy, $direction);
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];
    }

    public function getPatientReport($patienId, $reportType, $year)
    {
        if ('ppp' == $reportType) {
            return redirect()->route('get-ppp-report', [
                'userId' => $patienId,
                'year'   => $year,
            ]);
        }

        if ('provider-report' == $reportType) {
            return redirect()->route('get-provider-report', [
                'userId' => $patienId,
                'year'   => $year,
            ]);
        }

        throw new \Exception("Report type : [$reportType] does not exist.");
    }

    public function index()
    {
        return view('patientList');
    }

    /**
     * Create a patient manually, while creating a provider, if needed.
     * Update: Auto enroll into AWV.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePatientRequest $request, SurveyInvitationLinksService $service)
    {
        $providerUserId = $this->getPatientProvider($request);
        $result         = $this->createPatient($request, $service);
        $patientUserId  = $result['user_id'];

        CarePerson::updateOrCreate([
            'user_id'        => $patientUserId,
            'member_user_id' => $providerUserId,
        ], [
            'alert' => false,
            'type'  => CarePerson::BILLING_PROVIDER,
        ]);

        return response()->json($result);
    }

    /**
     * Create an AWV patient.
     *
     *
     * @return int|mixed
     */
    private function createPatient(StorePatientRequest $request, SurveyInvitationLinksService $service)
    {
        $patientInput      = $request->input('patient');
        $primaryPracticeId = $request->input('provider')['primaryPracticeId'];
        $user              = $this->createUser($patientInput, 'participant', $primaryPracticeId);

        $enrollSuccess = true;
        try {
            $service->enrolUserId($user->id);
        } catch (\Exception $e) {
            $enrollSuccess = false;
            $msg           = $e->getMessage();
            \Log::error("Patient created successfully, but there was an error enrolling user[$user->id] to AWV. ERROR: $msg");
        }

        return [
            'user_id'       => $user->id,
            'enrol_success' => $enrollSuccess,
        ];
    }

    private function createUser(array $input, string $roleName, $primaryPracticeId): User
    {
        //BASIC
        if (empty($input['email'])) {
            $input['email'] = $this->getRandomEmail();
        }

        $user = new User([
            'email'                => $input['email'],
            'user_registered'      => date('Y-m-d H:i:s'),
            'auto_attach_programs' => 0,
            'address'              => '',
            'address2'             => '',
            'city'                 => '',
            'state'                => '',
            'zip'                  => '',
            'program_id'           => $primaryPracticeId,
        ]);

        $user->status          = 'Active';
        $user->access_disabled = 1;
        $user->setFirstName($input['firstName']);
        $user->setLastName($input['lastName']);
        $user->username = $input['email'];
        $user->email    = $input['email'];
        $user->password = bcrypt(Str::random());
        $user->save();

        if ( ! empty($input['phoneNumber'])) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->setRawAttributes([
                'user_id'     => $user->id,
                'location_id' => 0, //not sure about this
                'type'        => null,
                'is_primary'  => false,
                'extension'   => null,
                'number'      => $this->formatPhoneNumber($input['phoneNumber']),
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]);
            $phoneNumber->save();
        }

        if ( ! empty($input['emrDirect'])) {
            $user->emr_direct_address = $input['emrDirect'];
        }

        //ROLES
        $roles = Role::getIdsFromNames([$roleName]);
        $user->attachRoleForPractice($roles, $primaryPracticeId);

        if ('participant' === $roleName) {
            //PATIENT INFO
            Patient::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'general_comment' => '',
                'ccm_status'      => Patient::NA,
                'birth_date'      => Carbon::parse($input['dob']),
                'is_awv'          => true,
            ]);

            if ( ! empty($input['appointment'])) {
                $appointment = Carbon::parse($input['appointment']);
                $user->addAppointment($appointment);
            }
        } else {
            $isClinical = 'non-clinical' === $input['suffix'];
            ProviderInfo::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'is_clinical' => $isClinical,
                'specialty'   => $input['specialty'],
            ]);
        }

        return $user;
    }

    private function formatPhoneNumber(string $numberString)
    {
        preg_match_all('/([\d]+)/', $numberString, $match);
        $sanitized = implode($match[0]);
        if (strlen($sanitized) < 10) {
            return '';
        }

        if (strlen($sanitized) > 10) {
            $sanitized = substr($sanitized, -10);
        }

        return substr($sanitized, 0, 3).'-'.substr($sanitized, 3, 3).'-'.substr($sanitized, 6, 4);
    }

    /**
     * Gets or creates a provider.
     *
     *
     * @return int the user id of the provider
     */
    private function getPatientProvider(StorePatientRequest $request): int
    {
        $providerInput = $request->input('provider');

        if ( ! empty($providerInput['id'])) {
            $providerUserId = $providerInput['id'];
        } else {
            $providerUser   = $this->createUser($providerInput, 'provider', $providerInput['primaryPracticeId']);
            $providerUserId = $providerUser->id;
        }

        return $providerUserId;
    }

    /**
     * Generate a random email. Needed when email for user is not supplied.
     *
     * @return string
     */
    private function getRandomEmail()
    {
        return 'awv_'.Str::random(20).'@careplanmanager.com';
    }
}
