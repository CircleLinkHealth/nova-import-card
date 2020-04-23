<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthyPhoneNumber;
use App\Http\Requests\VerifyAuthyTokenRequest;
use CircleLinkHealth\TwoFA\Http\Middleware\AuthyMiddleware;
use CircleLinkHealth\TwoFA\Services\AuthyService;

class AuthyController extends Controller
{
    /**
     * @var \CircleLinkHealth\TwoFA\Services\AuthyService
     */
    private $service;

    public function __construct(AuthyService $service)
    {
        $this->service = $service;
    }

    /**
     * Check the status of an approval request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOneTouchRequestStatus()
    {
        $user = auth()->user();

        if ($uuid = session('approval_request_uuid')) {
            $response = $this->service->checkOneTouchRequestStatus($uuid);
        }

        if ( ! isset($response)) {
            return $this->ok([
                'approval_request_status' => null,
            ]);
        }

        if (false === optional($response)->ok()) {
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
        $user      = auth()->user();
        $authyUser = $user->authyUser()->firstOrFail();

        $response = $this->service
            ->createOneTouchRequest($authyUser, $user);

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

    public function generateQrCode()
    {
        $user      = auth()->user();
        $authyUser = $user->authyUser()->first();
        if ( ! $authyUser) {
            $authyUser = $user->authyUser()->firstOrNew([]);
            $authyUser = $this->service
                ->register($authyUser, $user);
        }

        $response = $this->service
            ->generateQrCode($authyUser->authy_id, $user);

        if ($response->ok()) {
            return $this->ok([
                'message' => 'Verification Code sent via SMS succesfully.',
                'qr_code' => $response->bodyvar('qr_code'),
            ]);
        }

        return response()->json([
            'errors' => $response->errors(),
        ], 500);
    }

    public function sendTokenViaSms()
    {
        $authyUser = auth()->user()->authyUser()->firstOrFail();

        $response = $this->service
            ->sendTokenViaSms($authyUser->authy_id);

        if ($response->ok()) {
            return $this->ok(['message' => 'Verification Code sent via SMS succesfully.']);
        }

        return response()->json([
            'errors' => $response->errors(),
        ], 500);
    }

    public function sendTokenViaVoice()
    {
        $authyUser = auth()->user()->authyUser()->firstOrFail();

        $response = $this->service
            ->sendTokenViaVoice($authyUser->authy_id);

        if ($response->ok()) {
            return $this->ok(['message' => 'Verification Code sent via Voice Call succesfully.']);
        }

        return response()->json($response->errors(), 500);
    }

    public function showVerificationTokenForm()
    {
        return view('twofa::authy', [
            'authyUser'  => auth()->user()->authyUser,
            'redirectTo' => app('session.store')->get(AuthyMiddleware::SESSION_REDIRECT_KEY, '/'),
        ]);
    }

    /**
     * Register a user with Authy.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAuthyPhoneNumber $request)
    {
        $user      = auth()->user();
        $authyUser = $user->authyUser()->firstOrNew([]);

        $authyUser->phone_number = $request->input('phone_number');
        $authyUser->country_code = $request->input('country_code');

        $method = $request->input('method', null);
        if ($method) {
            $authyUser->authy_method = $method;
        }

        $isAuthyEnabled = $request->input('is_2fa_enabled', null);
        if (null !== $isAuthyEnabled) {
            $authyUser->is_authy_enabled = $isAuthyEnabled;
        }

        $authyUser->save();
        $authyUser = $this->service->register($authyUser, $user);

        if ( ! $authyUser->ok()) {
            return response()
                ->json([
                    'errors' => $authyUser->errors(),
                ], 400);
        }

        return response()
            ->json([], 201);
    }

    public function verifyToken(VerifyAuthyTokenRequest $request)
    {
        $token   = $request->input('token', null);
        $isSetup = $request->input('is_setup', false);
        $authyId = auth()->user()->authyUser->authy_id;

        $response = $this->service->verifyToken($authyId, $token, ! $isSetup);

        if ($response->ok()) {
            return $this->ok(['message' => 'Token verified successfully.']);
        }

        return response()->json($response->errors(), 400);
    }
}
