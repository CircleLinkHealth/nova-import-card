<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
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
     * @param $numberOfLocations
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateLocations(
        $numberOfLocations,
        $practiceId
    ) {
        $numberOfLocations = $numberOfLocations ?? 1;

        return view('provider.onboarding.create-locations', compact('numberOfLocations', 'practiceId'));
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
            foreach ($request->input('locations') as $newLocation) {
                $locations = $this->locations->create($newLocation);
            }
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->back();
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
                'email'      => $input['email'],
                'first_name' => $input['firstName'],
                'last_name'  => $input['lastName'],
                'password'   => bcrypt($input['password']),
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        $role = Role::whereName('practice-lead')->first();

        $user->roles()
            ->attach($role->id);

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
            $numberOfLocations = $input['numberOfLocations'];

            $practice = $this->practices
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

    /**
     * Store Staff.
     *
     * @param Request $request
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postStoreStaff(Request $request)
    {
        $input = $request->input();

        try {
            $numberOfLocations = $input['numberOfLocations'];

            $practice = $this->practices
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
