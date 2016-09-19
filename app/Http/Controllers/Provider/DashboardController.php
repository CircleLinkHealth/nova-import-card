<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\ProgramRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class DashboardController extends Controller
{
    protected $programs;
    protected $users;

    public function __construct(
        ProgramRepository $programRepository,
        UserRepository $userRepository
    )
    {
        $this->programs = $programRepository;
        $this->users = $userRepository;
    }

    public function getCreateLocation()
    {
        return view('provider.location.create');
    }

    public function getCreatePractice()
    {
        $practice = $this->programs->firstOrNew([
            'user_id' => auth()->user()->ID,
        ]);

        return view('provider.practice.create', compact('practice'));
    }

    public function getCreateUser()
    {
        return view('provider.user.create');
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard');
    }

    public function postStorePractice(Request $request)
    {
        $input = $request->input();

        try {
            $program = $this->programs->create([
                'name' => $input['url'],
                'user_id' => auth()->user()->ID,
                'display_name' => $input['name'],
                'description' => $input['description'],
                'domain' => "{$input['url']}.careplanmanager.com",
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->back();
    }

    public function postStoreUser(Request $request)
    {
        $input = $request->input();

        try {
            $user = $this->users->create([
                'user_email' => $input['email'],
                'first_name' => $input['firstName'],
                'last_name' => $input['lastName'],
                'password' => bcrypt($input['password']),
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
