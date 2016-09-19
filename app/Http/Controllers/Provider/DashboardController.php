<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class DashboardController extends Controller
{
    protected $users;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->users = $userRepository;
    }

    public function getCreateLocation()
    {
        return view('provider.location.create');
    }

    public function getCreatePractice()
    {
        return view('provider.practice.create');
    }

    public function getCreateUser()
    {
        return view('provider.user.create');
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard');
    }

    public function postStorePractice()
    {

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

        return redirect()->route('get.create.practice');
    }

}
