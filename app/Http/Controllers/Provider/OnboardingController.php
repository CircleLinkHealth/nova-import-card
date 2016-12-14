<?php

namespace App\Http\Controllers\Provider;

use App\CLH\Facades\StringManipulation;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Entities\Invite;
use App\Http\Controllers\Controller;
use App\Notifications\Onboarding\ImplementationLeadWelcome;
use App\Notifications\Onboarding\StaffInvite;
use App\PatientCareTeamMember;
use App\PhoneNumber;
use App\Role;
use App\User;
use Illuminate\Database\QueryException;
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
     * Show the form for an invited user to create their account.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateInvitedUser($code)
    {
        $invite = Invite::whereCode($code)
            ->first();

        if (!$invite) {
            abort(403);
        }

        $user = User::whereEmail($invite->email)
            ->first();

        return view('provider.onboarding.invited-staff', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form to create practice lead user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePracticeLeadUser($code)
    {
        $invite = Invite::whereCode($code)
            ->first();

        if (!$invite) {
            abort(403);
        }

        return view('provider.onboarding.create-practice-lead', compact('invite'));
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

        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $primaryPractice->users->map(function ($user) {
            return [
                'id'                 => $user->id,
                'email'              => $user->email,
                'last_name'          => $user->last_name,
                'first_name'         => $user->first_name,
                'phone_number'       => '',
                'phone_type'         => '',
                'isComplete'         => false,
                'validated'          => false,
                'grandAdminRights'   => false,
                'sendBillingReports' => false,
                'errorCount'         => 0,
                'role_id'            => 0,
            ];
        });

        //get the relevant roles
        $roles = Role::whereIn('name', [
            'med_assistant',
            'office_admin',
            'practice-lead',
            'provider',
            'registered-nurse',
            'specialist',
        ])->get([
            'id',
            'display_name',
        ])
            ->sortBy('display_name');

        \JavaScript::put([
            'existingUsers' => $existingUsers,
            'locations'     => $primaryPractice->locations,
            'phoneTypes'    => PhoneNumber::getTypes(),
            'roles'         => $roles->all(),
            //this will help us get role names on the views: rolesMap[id]
            'rolesMap'      => $roles->keyBy('id')->all(),
        ]);

        return view('provider.onboarding.create-staff-users');
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

            $primaryPractice = $this->practices
                ->skipPresenter()
                ->findWhere([
                    'user_id' => auth()->user()->id,
                ])->first();

            foreach ($request->input('locations') as $newLocation) {

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

                //If clinical contact is same for all, then get the data from the first location.
                if ($sameClinicalContact) {
                    $newLocation['clinical_contact']['type'] = $request->input('locations')[0]['clinical_contact']['type'];
                    $newLocation['clinical_contact']['email'] = $request->input('locations')[0]['clinical_contact']['email'];
                    $newLocation['clinical_contact']['firstName'] = $request->input('locations')[0]['clinical_contact']['firstName'];
                    $newLocation['clinical_contact']['lastName'] = $request->input('locations')[0]['clinical_contact']['lastName'];
                }

                if ($newLocation['clinical_contact']['type'] == PatientCareTeamMember::BILLING_PROVIDER) {
                    //do nothing
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
                ->withInput()
                ->withErrors($e->getMessageBag()->getMessages());
        }

        return redirect()->route('get.onboarding.create.staff', [
            'practiceSlug' => $primaryPractice->name,
        ]);
    }

    /**
     * Store Invited User
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStoreInvitedUser(Request $request)
    {
        $input = $request->input();

        try {
            $user = $this->users->skipPresenter()->create([
                'email'      => $input['email'],
                'first_name' => $input['firstName'],
                'last_name'  => $input['lastName'],
                'password'   => bcrypt($input['password']),
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($e->getMessageBag()->getMessages());
        }

        auth()->login($user);

        return redirect()->route('/');
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

        //Create the User
        try {
            $user = $this->users->skipPresenter()->create([
                'email'          => $input['email'],
                'first_name'     => $input['firstName'],
                'last_name'      => $input['lastName'],
                'password'       => bcrypt($input['password']),
                'count_ccm_time' => isset($input['countCcmTime'])
                    ? (bool)$input['countCcmTime']
                    : false,
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($e->getMessageBag()->getMessages());
        }

        //Attach role
        $role = Role::whereName('practice-lead')->first();

        $user->roles()
            ->attach($role->id);

        auth()->login($user);

        if (isset($input['code'])) {
            $invite = Invite::whereCode($input['code'])
                ->first();

            if ($invite) {
                $invite->delete();
            }
        }

        return redirect()->route('get.onboarding.create.practice');
    }

    /**
     * Store a Practice.
     *
     * @param Request $request
     *
     * @return OnboardingController|\Illuminate\Http\RedirectResponse
     */
    public function postStorePractice(Request $request)
    {
        $input = $request->input();

        try {
            $practice = $this->practices
                ->skipPresenter()
                ->create([
                    'name'           => str_slug($input['name']),
                    'user_id'        => auth()->user()->id,
                    'display_name'   => $input['name'],
                    'federal_tax_id' => $input['federal_tax_id'],
                ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        auth()->user()->program_id = $practice->id;
        auth()->user()->save();

        return redirect()->route('get.onboarding.create.locations', [
            'practiceSlug' => $practice->name,
        ]);
    }

    /**
     * Store Staff.
     *
     * @param Request $request
     *
     * @return OnboardingController|array|\Illuminate\Http\RedirectResponse
     */
    public function postStoreStaff(Request $request)
    {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'user_id' => auth()->user()->id,
            ])->first();

        $implementationLead = $primaryPractice->lead;

        $adminRole = Role::whereName('practice-lead')
            ->first();

        foreach ($request->input('deleteTheseUsers') as $id) {
            $this->users->delete($id);
        }

        foreach ($request->input('users') as $index => $newUser) {
            //create the user
            try {
                if (isset($newUser['id'])) {
                    $user = $this->users
                        ->skipPresenter()
                        ->update([
                            'program_id'   => $primaryPractice->id,
                            'email'        => $newUser['email'],
                            'first_name'   => $newUser['first_name'],
                            'last_name'    => $newUser['last_name'],
                            'password'     => 'password_not_set',
                            'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                        ], $newUser['id']);
                } else {
                    $user = $this->users
                        ->skipPresenter()
                        ->create([
                            'program_id'   => $primaryPractice->id,
                            'email'        => $newUser['email'],
                            'first_name'   => $newUser['first_name'],
                            'last_name'    => $newUser['last_name'],
                            'password'     => 'password_not_set',
                            'display_name' => "{$newUser['first_name']} {$newUser['last_name']}",
                        ]);
                }

                //Attach the role
                $user->roles()->attach($newUser['role_id']);

                if ($newUser['grand_admin_rights']) {
                    $user->roles()->attach($adminRole);
                }

                //Attach the locations
                foreach ($newUser['locations'] as $locId) {
                    if (!$user->locations->contains($locId)) {
                        $user->locations()->attach($locId);
                    }
                }

                //attach phone
                $user->phoneNumbers()->create([
                    'number'     => StringManipulation::formatPhoneNumber($newUser['phone_number']),
                    'type'       => PhoneNumber::getTypes()[$newUser['phone_type']],
                    'is_primary' => true,
                ]);

                $user->notify(new StaffInvite($implementationLead, $primaryPractice));
            } catch (\Exception $e) {
                if ($e instanceof QueryException) {
                    $errorCode = $e->errorInfo[1];
                    if ($errorCode == 1062) {
                        //do nothing
                        //we don't actually want to terminate the program if we detect duplicates
                        //we just don't wanna add the row again
                    }
                } elseif ($e instanceof ValidatorException) {
                    $errors[] = [
                        'index'    => $index,
                        'messages' => $e->getMessageBag()->getMessages(),
                        'input'    => $newUser,
                    ];
                }
            }
        }

        if (isset($errors)) {
            return response()->json([
                'errors' => $errors,
            ], 400);
        }

        $implementationLead->notify(new ImplementationLeadWelcome($primaryPractice));

        return view('provider.onboarding.welcome');
    }
}
