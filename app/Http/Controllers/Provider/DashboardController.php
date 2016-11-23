<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class DashboardController extends Controller
{
    protected $invites;
    protected $locations;
    protected $practices;
    protected $users;

    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        PracticeRepository $practiceRepository,
        UserRepository $userRepository,
        Request $request
    ) {
        parent::__construct($request);

        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users = $userRepository;
    }

    public function getCreateLocation()
    {
        $locations[] = $this->locations->firstOrNew([]);

        return view('provider.location.create', compact('locations'));
    }

    public function getCreatePractice()
    {
        $practice = $this->practices->firstOrNew([
            'user_id' => auth()->user()->id,
        ]);

        return view('provider.practice.create', compact('practice'));
    }

    public function getCreateStaff()
    {
        $invite = $this->invites->firstOrNew([
            'inviter_id' => auth()->user()->id,
        ]);

        return view('provider.user.create-staff', compact('invite'));
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard');
    }

    public function postStoreInvite(Request $request)
    {
        $invite = $this->invites->create([
            'inviter_id' => auth()->user()->id,
            'role_id'    => $request->input('role'),
            'email'      => $request->input('email'),
            'subject'    => $request->input('subject'),
            'message'    => $request->input('message'),
            'code'       => str_random(20),
        ]);
    }

    public function postStoreLocation(Request $request)
    {

    }

    public function postStorePractice(Request $request)
    {
        $input = $request->input();

        try {
            $program = $this->practices->create([
                'name'         => str_slug($input['name']),
                'user_id'      => auth()->user()->id,
                'display_name' => $input['name'],
                'description'  => $input['description'],
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->back();
    }
}
