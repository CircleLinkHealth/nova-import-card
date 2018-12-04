<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
     *   @SWG\GET(
     *     path="/profile",
     *     tags={"user"},
     *     summary="Get User Info",
     *     description="Get Basic User Information",
     *     @SWG\Response(
     *         response="default",
     *         description="Basic User Information"
     *     )
     *   )
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $role = $user->practiceOrGlobalRole();

        $data = [
            'user' => [
                'id'         => $user->id,
                'program_id' => $user->program_id,
                'username'   => $user->username,
                'first_name' => $user->getFirstName(),
                'last_name'  => $user->getLastName(),
                'email'      => $user->email,
                'role'       => [
                    'id'   => $role->id,
                    'name' => $role->name,
                ],
            ],
        ];

        return response()->json($data);
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
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }
}
