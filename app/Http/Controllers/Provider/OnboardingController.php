<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Entities\Invite;
use App\Http\Controllers\Controller;
use App\Role;
use App\Services\OnboardingService;
use App\User;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;


class OnboardingController extends Controller
{
    /**
     * The User's invite, if one exists.
     *
     * @var Invite
     */
    protected $invite;

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
     * @var OnboardingService
     */
    protected $onboardingService;

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
        UserRepository $userRepository,
        OnboardingService $onboardingService,
        Request $request
    ) {
        parent::__construct($request);

        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users = $userRepository;
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
            'user' => $user,
        ]);
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
     * Show the form to create Locations
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param $numberOfLocations
     *
     */
    public function getCreateLocations(
        $practiceSlug,
        $leadId
    ) {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'name' => $practiceSlug,
            ])->first();

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingLocations($primaryPractice);

        return view('provider.onboarding.create-locations', compact(['leadId']));
    }

    /**
     * Show the form to create a practice
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreatePractice($leadId)
    {
        return view('provider.onboarding.create-practice', compact(['leadId']));
    }


    /**
     * Show the form to create staff members
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateStaff($practiceSlug)
    {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'name' => $practiceSlug,
            ])->first();

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingStaff($primaryPractice);

        return view('provider.onboarding.create-staff-users', compact(['practiceSlug']));
    }

    /**
     * Store locations.
     *
     * @param Request $request
     */
    public function postStoreLocations(
        Request $request,
        $leadId
    ) {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'user_id' => $leadId,
            ])->first();

        $this->onboardingService->postStoreLocations($primaryPractice, $request);

        return response()->json([
            'redirect_to' => route('get.onboarding.create.staff', [
                'practiceSlug' => $primaryPractice->name,
            ]),
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
                'password'       => $input['password'],
                'count_ccm_time' => isset($input['countCcmTime'])
                    ? (bool)$input['countCcmTime']
                    : false,
            ]);

            $user->password = bcrypt($user->password);
            $user->save();
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

        if (!auth()->user()->hasRole('salesperson')) {
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
     * Store a Practice.
     *
     * @param Request $request
     *
     * @return OnboardingController|\Illuminate\Http\RedirectResponse
     */
    public function postStorePractice(
        Request $request,
        $leadId
    ) {
        $input = $request->input();

        $lead = User::find($leadId);

        try {
            $practice = $this->practices
                ->skipPresenter()
                ->create([
                    'name'           => str_slug($input['name']),
                    'user_id'        => $lead->id,
                    'display_name'   => $input['name'],
                    'federal_tax_id' => $input['federal_tax_id'],
                ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        $lead->program_id = $practice->id;
        $lead->save();

        $leadRole = Role::whereName('practice-lead')->first();

        $attachPractice = $lead->attachPractice($practice, true, true, $leadRole);

        return redirect()->route('get.onboarding.create.locations', [
            'practiceSlug' => $practice->name,
            'lead_id'      => $lead->id,
        ]);
    }

    /**
     * Store Staff.
     *
     * @param Request $request
     *
     * @return OnboardingController|array|\Illuminate\Http\RedirectResponse
     */
    public function postStoreStaff(
        Request $request,
        $practiceSlug
    ) {
        $primaryPractice = $this->practices
            ->skipPresenter()
            ->findWhere([
                'name' => $practiceSlug,
            ])->first();

        $this->onboardingService->postStoreStaff($primaryPractice, $request);

//        $implementationLead->notify(new ImplementationLeadWelcome($primaryPractice));

        return view('provider.onboarding.welcome');
    }
}
