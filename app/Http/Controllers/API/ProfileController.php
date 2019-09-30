<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

class ProfileController extends ApiController
{
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
}
