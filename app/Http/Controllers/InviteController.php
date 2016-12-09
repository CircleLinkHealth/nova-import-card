<?php

namespace App\Http\Controllers;

use App\Entities\Invite;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->input();

        $exists = Invite::whereEmail($input['email'])->first();

        if(!empty($exists)){
            $message = $input['email'] . " has already been invited.";
            return view('admin.invites.create', ['message' => $message]);
        }

        //@todo actually send email

        $invite = Invite::create([

            'inviter_id' => auth()->user()->id,
            'email' => $input['email'],
            'subject' => $input['subject'],
            'message' => $input['body'],
            'code' => generateRandomString(20)

        ]);

        return view('admin.invites.create', ['message' => "Invite sent to: $invite->email"]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
