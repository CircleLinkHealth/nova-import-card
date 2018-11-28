<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthyPhoneNumber;
use App\Http\Requests\VerifyAuthyTokenRequest;
use App\Services\AuthyService;
use Illuminate\Http\Request;

class AuthyController extends Controller
{
    /**
     * @var AuthyService
     */
    private $service;

    public function __construct(AuthyService $service)
    {
        $this->service = $service;
    }

    public function showVerificationTokenForm()
    {
        return view('auth.authy');
    }

    /**
     * Register a user with Authy
     *
     * @param StoreAuthyPhoneNumber $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAuthyPhoneNumber $request)
    {
        $user = auth()->user();

        $user->phone_number     = $request->input('phone_number');
        $user->country_code     = $request->input('country_code');
        $user->authy_method     = $request->input('method');
        $user->is_authy_enabled = $request->input('is_2fa_enabled');
        $user->save();

        $authyUser = $this->service
            ->register($user);

        if ( ! $authyUser->ok()) {
            return response()
                ->json([
                    'errors' => $authyUser->errors(),
                ], 400);
        }

        return response()
            ->json([], 201);
    }

    /**
     * Check the status of an approval request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOneTouchRequestStatus()
    {
        $user = auth()->user();

        if ($uuid = session('approval_request_uuid')) {
            $response = $this->service->checkOneTouchRequestStatus($uuid);
        }

        if ( ! $response->ok()) {
            return response()
                ->json([
                    'errors' => $response->errors(),
                ], 400);
        }

        return $this->ok([
            'approval_request_status' => session('authy_status'),
        ]);
    }

    /**
     * Send an approval request to the User's phone.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOneTouchRequest()
    {
        $user = auth()->user();

        $response = $this->service
            ->createOneTouchRequest($user);

        if ( ! $response->ok()) {
            return response()
                ->json([
                    'errors' => $response->errors(),
                ], 400);
        }

        return $this->ok([
            'approval_request_uuid' => session('approval_request_uuid'),
        ]);
    }

    public function verifyToken(VerifyAuthyTokenRequest $request)
    {
        $data    = $request->all();
        $token   = $data['token'];
        $authyId = auth()->user()->authy_id;

        $response = $this->service->verifyToken($authyId, $token);

        if ($response->ok()) {
            return $this->ok(['message' => 'Token verified successfully.']);
        }

        return response()->json($response->errors(), 500);
    }

    public function sendTokenViaSms()
    {
        $response = $this->service
            ->sendTokenViaSms(auth()->user()->authy_id);

        if ($response->ok()) {
            return $this->ok(['message' => 'Verification Code sent via SMS succesfully.']);
        }

        return response()->json([
            'errors' => $response->errors(),
        ], 500);
    }

    public function sendTokenViaVoice()
    {
        $response = $this->service
            ->sendTokenViaVoice(auth()->user()->authy_id);

        if ($response->ok()) {
            return $this->ok(['message' => 'Verification Code sent via Voice Call succesfully.']);
        }

        return response()->json($response->errors(), 500);
    }
}
