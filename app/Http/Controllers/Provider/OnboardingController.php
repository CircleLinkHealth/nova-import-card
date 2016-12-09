<?php

namespace App\Http\Controllers\Provider;

use App\CLH\Facades\StringManipulation;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\PatientCareTeamMember;
use App\Role;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;


class OnboardingController extends Controller
{
    /**
     * @var InviteRepository
     */
    protected $invites;

    /**
     * @var LocationRepository
     */
    protected $locations;

    /**
     * @var PracticeRepository
     */
    protected $practices;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * OnboardingController constructor.
     *
     * @param InviteRepository $inviteRepository
     * @param LocationRepository $locationRepository
     * @param PracticeRepository $practiceRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        PracticeRepository $practiceRepository,
        UserRepository $userRepository
    ) {
        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users = $userRepository;
    }

    /**
     * Show the form to create practice lead user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePracticeLeadUser()
    {
        return view('provider.onboarding.create-practice-lead');
    }

    /**
     * Show the form to create Locations
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param $numberOfLocations
     *
     */
    public function getCreateLocations()
    {
        return view('provider.onboarding.create-locations');
    }

    /**
     * Show the form to create a practice
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePractice()
    {
        return view('provider.onboarding.create-practice');
    }


    /**
     * Show the form to create staff members
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateStaff()
    {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'user_id' => auth()->user()->id,
            ])->first();


        $existingUsers = $primaryPractice->users->map(function ($user) {
            return [
                'id'         => $user->id,
                'role'       => [
                    'id'   => 0,
                    'name' => 'No Role Selected',
                ],
                'email'      => $user->email,
                'last_name'  => $user->last_name,
                'first_name' => $user->first_name,
            ];
        });

        \JavaScript::put([
            'existingUsers' => $existingUsers,
        ]);

        return view('provider.onboarding.create-staff-users', [
            'locations' => $primaryPractice->locations,
        ]);
    }

    /**
     * Store locations.
     *
     * @param Request $request
     */
    public function postStoreLocations(Request $request)
    {
        try {
            $sameEHRLogin = isset($request->input('locations')[0]['same_ehr_login']);
            $sameClinicalContact = isset($request->input('locations')[0]['same_clinical_contact']);

            foreach ($request->input('locations') as $newLocation) {

                $primaryPractice = $this->practices
                    ->skipPresenter()
                    ->findWhere([
                        'user_id' => auth()->user()->id,
                    ])->first();

                $location = $this->locations
                    ->skipPresenter()
                    ->create([
                        'practice_id'    => $primaryPractice->id,
                        'name'           => $newLocation['name'],
                        'phone'          => StringManipulation::formatPhoneNumber($newLocation['phone']),
                        'address_line_1' => $newLocation['address_line_1'],
                        'address_line_2' => $newLocation['address_line_2'],
                        'city'           => $newLocation['city'],
                        'state'          => $newLocation['state'],
                        'timezone'       => $newLocation['timezone'],
                        'postal_code'    => $newLocation['postal_code'],
                        'ehr_login'      => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_login']
                            : $newLocation['ehr_login'],
                        'ehr_password'   => $sameEHRLogin
                            ? $request->input('locations')[0]['ehr_password']
                            : $newLocation['ehr_password'],
                    ]);

                if ($sameClinicalContact) {

                }

                if ($newLocation['clinical_contact']['type'] == PatientCareTeamMember::BILLING_PROVIDER) {

                } else {
                    $user = $this->users->create([
                        'program_id' => $primaryPractice->id,
                        'email'      => $newLocation['clinical_contact']['email'],
                        'first_name' => $newLocation['clinical_contact']['firstName'],
                        'last_name'  => $newLocation['clinical_contact']['lastName'],
                        'password'   => 'password_not_set',
                    ]);

                    $user->attachPractice($primaryPractice);
                    $user->attachLocation($location);

                    $location->clinicalEmergencyContact()->attach($user->id, [
                        'name' => $newLocation['clinical_contact']['type'],
                    ]);
                }
            }
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($e->getMessageBag()->getMessages());
        }

        return redirect()->route('get.onboarding.create.staff');
    }

    /**
     * Store Practice Lead User
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStorePracticeLeadUser(Request $request)
    {
        $input = $request->input();

        try {
            $user = $this->users->skipPresenter()->create([
                'email'          => $input['email'],
                'first_name'     => $input['firstName'],
                'last_name'      => $input['lastName'],
                'password'       => bcrypt($input['password']),
                'count_ccm_time' => (bool)$input['countCcmTime'],
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($e->getMessageBag()->getMessages());
        }

        $role = Role::whereName('practice-lead')->first();

        $user->roles()
            ->attach($role->id);

        auth()->login($user);

        return redirect()->route('get.onboarding.create.practice');
    }

    /**
     * Store a Practice.
     *
     * @param Request $request
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postStorePractice(Request $request)
    {
        $input = $request->input();

        try {
            $practice = $this->practices
                ->skipPresenter()
                ->create([
                    'name'         => str_slug($input['name']),
                    'user_id'      => auth()->user()->id,
                    'display_name' => $input['name'],
                ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->route('get.onboarding.create.locations');
    }

    /**
     * Store Staff.
     *
     * @param Request $request
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postStoreStaff(Request $request)
    {
        return view('provider.onboarding.welcome');

        $input = $request->input();

        try {
            $practice = $this->users
                ->skipPresenter()
                ->create([
                    'name'         => str_slug($input['name']),
                    'user_id'      => auth()->user()->id,
                    'display_name' => $input['name'],
                ]);

            $practiceId = $practice->id;
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->route('get.onboarding.create.locations', compact('numberOfLocations', 'practiceId'));
    }
}
