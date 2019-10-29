<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Provider;

use Illuminate\Support\Str;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInvitedUserAccount;
use App\Services\OnboardingService;
use CircleLinkHealth\Customer\Entities\Invite;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    /**
     * The User's invite, if one exists.
     *
     * @var \CircleLinkHealth\Customer\Database\Migrations\Invite
     */
    protected $invite;

    /**
     * @var OnboardingService
     */
    protected $onboardingService;

    /**
     * OnboardingController constructor.
     *
     * @param InviteRepository   $inviteRepository
     * @param LocationRepository $locationRepository
     * @param OnboardingService  $onboardingService
     * @param Request            $request
     */
    public function __construct(
        OnboardingService $onboardingService,
        Request $request
    ) {
        $this->onboardingService = $onboardingService;

        if ($request->route('code')) {
            $this->invite = Invite::whereCode($request->route('code'))->first();
        }
    }

    /**
     * Show the form for an invited user to create their account.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateInvitedUser()
    {
        $user = User::whereEmail($this->invite->email)
            ->first();

        return view('provider.onboarding.invited-staff', [
            'user'   => $user,
            'invite' => $this->invite,
        ]);
    }

    /**
     * Show the form to create Locations.
     *
     * @internal param $numberOfLocations
     *
     * @param mixed $practiceSlug
     * @param mixed $leadId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateLocations(
        $practiceSlug,
        $leadId
    ) {
        $primaryPractice = $this->findPracticeByName($practiceSlug);

        if ( ! $primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingLocations($primaryPractice);

        return view('provider.onboarding.create-locations', compact(['leadId']));
    }

    /**
     * Show the form to create a practice.
     *
     * @param mixed $leadId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePractice($leadId)
    {
        return view('provider.onboarding.create-practice', compact(['leadId']));
    }

    /**
     * Show the form to create practice lead user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePracticeLeadUser()
    {
        $invite = $this->invite ?? new Invite();

        return view('provider.onboarding.create-practice-lead', compact('invite'));
    }

    /**
     * Show the form to create staff members.
     *
     * @param mixed $practiceSlug
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateStaff($practiceSlug)
    {
        $primaryPractice = $this->findPracticeByName($practiceSlug);

        if ( ! $primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingStaff($primaryPractice);

        return view('provider.onboarding.create-staff-users', compact(['practiceSlug']));
    }

    /**
     * Store Invited User.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStoreInvitedUser(CreateInvitedUserAccount $request)
    {
        $user = User::updateOrCreate([
            'id' => $request['userId'],
        ], [
            'email'      => $request['email'],
            'first_name' => $request['first_name'],
            'last_name'  => $request['last_name'],
            'password'   => bcrypt($request['password']),
        ]);

        auth()->login($user);

        Invite::whereCode($request['code'])
            ->delete();

        return redirect()
            ->to('/');
    }

    /**
     * Store locations.
     *
     * @param Request $request
     * @param mixed   $leadId
     */
    public function postStoreLocations(
        Request $request,
        $leadId
    ) {
        $primaryPractice = Practice::where('user_id', $leadId)->first();

        $this->onboardingService->postStoreLocations($primaryPractice, $request);

        return response()->json([
            'redirect_to' => route('get.onboarding.create.staff', [
                'practiceSlug' => $primaryPractice->name,
            ]),
        ]);
    }

    /**
     * Store a Practice.
     *
     * @param Request $request
     * @param mixed   $leadId
     *
     * @return \Illuminate\Http\RedirectResponse|OnboardingController
     */
    public function postStorePractice(
        Request $request,
        $leadId
    ) {
        $input = $request->input();

        $lead = User::findOrFail($leadId);

        try {
            $this->validate($request, ['name' => 'required|unique:practices,name']);

            $practice = Practice::create([
                'name'           => Str::slug($input['name']),
                'user_id'        => $lead->id,
                'display_name'   => $input['name'],
                'federal_tax_id' => $input['federal_tax_id'],
                'active'         => 1,
                'term_days'      => 30,
            ]);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        $lead->program_id = $practice->id;
        $lead->save();

        $leadRole = Role::whereName('practice-lead')->first();
        $lead->attachPractice($practice, [$leadRole->id], true);

        return redirect()->route('get.onboarding.create.locations', [
            'practiceSlug' => $practice->name,
            'lead_id'      => $lead->id,
        ]);
    }

    /**
     * Store Practice Lead User.
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
            $this->validate($request, [
                [
                    'email'      => 'required|email|unique:users,email',
                    'first_name' => 'required',
                    'last_name'  => 'required',
                    'password'   => 'required|min:8',
                ],
            ]);

            $user = new User([
                'email'          => $input['email'],
                'first_name'     => $input['firstName'],
                'last_name'      => $input['lastName'],
                'password'       => $input['password'],
                'count_ccm_time' => isset($input['countCcmTime'])
                    ? (bool) $input['countCcmTime']
                    : false,
            ]);

            $user->password = bcrypt($user->password);
            $user->save();
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput($input)
                ->withErrors($e->errors());
        }

        //Attach role
        $role = Role::whereName('practice-lead')->first();

        $user->roles()
            ->attach($role->id);

        if ( ! auth()->user()->hasRole('salesperson')) {
            auth()->login($user);
        }

        if (isset($input['code'])) {
            $invite = Invite::whereCode($input['code'])
                ->first();

            if ($invite) {
                $invite->delete();
            }
        }

        return redirect()->route('get.onboarding.create.practice', [
            'lead_id' => $user->id,
        ]);
    }

    /**
     * Store Staff.
     *
     * @param Request $request
     * @param mixed   $practiceSlug
     *
     * @return array|\Illuminate\Http\RedirectResponse|OnboardingController
     */
    public function postStoreStaff(
        Request $request,
        $practiceSlug
    ) {
        $primaryPractice = $this->findPracticeByName($practiceSlug);

        $this->onboardingService->postStoreStaff($primaryPractice, $request);

//        $implementationLead->notify(new ImplementationLeadWelcome($primaryPractice));

        return view('provider.onboarding.welcome');
    }

    private function findPracticeByName($practiceSlug)
    {
        return Practice::where('name', $practiceSlug)->first();
    }
}
