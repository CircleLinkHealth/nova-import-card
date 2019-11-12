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
            //need to create the $providerUser
            $providerUser = User::create([
                'first_name' => $providerInput['first_name'],
                'last_name'  => $providerInput['last_name'],
                'suffix'     => $providerInput['suffix'] !== 'non-clinical'
                    ? $providerInput['suffix']
                    : null,
                'address'    => $providerInput['address'],
                'address2'   => $providerInput['address2'],
                'city'       => $providerInput['city'],
                'state'      => $providerInput['state'],
                'zip'        => $providerInput['zip'],
                'email'      => $providerInput['email'],
                'program_id' => $providerInput['primary_practice']['id'],
            ]);
            $providerUser->save();

            ProviderInfo::updateOrCreate([
                'user_id' => $providerUser->id,
            ], [
                'is_clinical' => $providerInput['suffix'] !== 'non-clinical',
                'specialty'   => $providerInput['specialty'],
            ]);

            if (isset($providerInput['phone_numbers'][0])) {
                $providerUser->clearAllPhonesAndAddNewPrimary($providerInput['phone_numbers'][0], null, true);
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
        $user         = User::create([
            'first_name'      => $patientInput['first_name'],
            'last_name'       => $patientInput['last_name'],
            'email'           => $patientInput['email'],
            'user_registered' => date('Y-m-d H:i:s'),
        ]);
        $user->createNewUser($patientInput['email'], str_random());
        $user->clearAllPhonesAndAddNewPrimary($patientInput['phone_number'], null, true);

        Patient::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'ccm_status' => Patient::NA,
            'birth_date' => Carbon::parse($patientInput['dob']),
            'is_awv'     => true,
        ]);

        return $user->id;
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
