<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    protected $user;

    public function __construct(UserRepository $userRepository)
    {
        $this->user = $userRepository;
    }

    /**
     * This function will authenticate the user using their username and password and return an access token.
     *
     * @param Request $request
     * @return string access_token
     */
    public function getAccessToken(Request $request)
    {
        if (!$request->filled('username') || !$request->filled('password')) {
            response()->json(['error' => 'Username and password need to be included on the request.'], 400);
        }

        $user = $this->user->findWhere([
            'email' => $request->input('username'),
        ])->first();

        if (empty($user)) {
            return response()->json(['error' => 'Invalid Credentials.'], 400);
        }


        //Verify Role for UPG
        if (!$user->hasRoleForSite('aprima-api-location', 16)) {
            return response()->json(['error' => 'Invalid Credentials.'], 400);
        }

        //Transform the credentials to match our JWT Auth. They were using EHR API, remember?
        $credentials = [
            'email'    => $request->input('username'),
            'password' => $request->input('password'),
        ];

        \JWTAuth::setIdentifier('id');

        if (!$access_token = \JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials.'], 400);
        }

        return response()->json(compact('access_token'), 200);
    }
}
