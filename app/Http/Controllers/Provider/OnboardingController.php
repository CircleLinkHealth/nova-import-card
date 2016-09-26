<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\ProgramRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class OnboardingController extends Controller
{
    protected $invites;
    protected $locations;
    protected $programs;
    protected $users;

    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        ProgramRepository $programRepository,
        UserRepository $userRepository
    ) {
        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->programs = $programRepository;
        $this->users = $userRepository;
    }

    public function getCreateProgramLeadUser()
    {
        return view('provider.onboarding.create-program-lead');
    }


    public function getCreateLocations($numberOfLocations)
    {
        return view('provider.onboarding.create-locations', compact('numberOfLocations'));
    }


    public function getCreatePractice()
    {
        return view('provider.onboarding.create-practice');
    }

    public function postStoreLocations(Request $request)
    {
        dd($request->input());
    }

    public function postStoreProgramLeadUser(Request $request)
    {
        $input = $request->input();

        try {
            $user = $this->users->create([
                'user_email' => $input['email'],
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

        $role = Role::whereName('program-lead')->first();

        $user->roles()->attach($role->id);

        auth()->login($user);

        return redirect()->route('get.onboarding.create.practice');
    }

    public function postStorePractice(Request $request)
    {
        $input = $request->input();

        try {
            $numberOfLocations = $input['numberOfLocations'];

            $program = $this->programs->create([
                'name'         => str_slug($input['name']),
                'user_id'      => auth()->user()->ID,
                'display_name' => $input['name'],
                'description'  => $input['description'],
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->route('get.onboarding.create.locations', compact('numberOfLocations'));
    }
}
