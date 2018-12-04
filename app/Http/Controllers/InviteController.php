<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Entities\Invite;
use App\Notifications\Onboarding\ImplementationLeadInvite;
use App\Role;
use App\User;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.invites.create');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->input();

        $exists = Invite::whereEmail($input['email'])->first();

        if ( ! empty($exists)) {
            $message = $input['email'].' has already been invited.';

            return view('admin.invites.create', ['message' => $message]);
        }

        $invite = Invite::create([
            'inviter_id' => auth()->user()->id,
            'role_id'    => Role::whereName('practice-lead')->first()->id,
            'email'      => $input['email'],
            'subject'    => $input['subject'],
            'message'    => $input['body'],
            'code'       => generateRandomString(20),
        ]);

        $user = new User(['email' => $invite->email]);

        \Illuminate\Support\Facades\Notification::send([$user], new ImplementationLeadInvite($invite));

        return view('admin.invites.create', ['message' => "Invite sent to: {$invite->email}"]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        $id
    ) {
    }
}
