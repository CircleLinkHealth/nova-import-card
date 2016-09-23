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
        return view('provider.user.create-program-lead');
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

        return redirect()->route('get.create.practice');
    }
}
