<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthyPhoneNumber;
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
    public function checkApprovalRequestStatus()
    {
        $user = auth()->user();

        $response = $this->service->checkApprovalRequestStatus(session('approval_request_uuid'));

        if ( ! $response->ok()) {
            return response()
                ->json([
                    'errors' => $response->errors(),
                ], 400);
        }

        return $this->ok();
    }

    /**
     * Send an approval request to the User's phone.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createApprovalRequest()
    {
        $user = auth()->user();

        $response = $this->service
            ->createApprovalRequest($user);

        if ( ! $response->ok()) {
            return response()
                ->json([
                    'errors' => $response->errors(),
                ], 400);
        }

        return $this->ok();
    }
}
