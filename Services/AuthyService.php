<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Services;

use Authy\AuthyUser as AuthyApiUser;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TwoFA\Contracts\AuthyApiable;
use CircleLinkHealth\TwoFA\Entities\AuthyUser;

class AuthyService
{
    /**
     * @var AuthyApiable
     */
    private $api;

    /**
     * AuthyService constructor.
     */
    public function __construct(AuthyApiable $authyApi)
    {
        $this->api = $authyApi;
    }

    /**
     * Check approval request Status.
     *
     * @param $approvalRequestUuid
     *
     * @return \Authy\AuthyResponse
     */
    public function checkOneTouchRequestStatus($approvalRequestUuid)
    {
        $response = $this->api
            ->getApprovalRequest($approvalRequestUuid);

        if ($response->ok()) {
            $approval_request = (array) $response->bodyvar('approval_request');
            $this->on2FASuccess($approval_request['status']);
        }

        return $response;
    }

    /**
     * Create approval request.
     *
     * @return \Authy\AuthyResponse
     */
    public function createOneTouchRequest(AuthyUser $authyUser, User $user)
    {
        $response = $this
            ->api
            ->createApprovalRequest(
                $authyUser->authy_id,
                'Login to CarePlan Manager.',
                [
                    'seconds_to_expire' => 120,
                    'details'           => [
                        'Username' => $user->username,
                        'Site'     => config('opcache.url'),
                    ],
                ]
            );

        if ($response->ok()) {
            $approval_request = (array) $response->bodyvar('approval_request');
            session(['approval_request_uuid' => $approval_request['uuid']]);
        }

        return $response;
    }

    public function generateQrCode($authyId, User $user)
    {
        $appName = config('app.name');
        $opts    = [
            'label'   => "$appName($user->email)",
            'qr_size' => AuthyApiable::QR_DEFAULT_SIZE,
        ];

        return $this->api->requestQrCode($authyId, $opts);
    }

    /**
     * Register a User with Authy.
     *
     * @return AuthyApiUser
     */
    public function register(AuthyUser $authyUser, User $user)
    {
        $response = $this->api
            ->registerUser(
                $user->email,
                $authyUser->phone_number,
                $authyUser->country_code,
                'app' == $authyUser->authy_method
            );

        if ($response->ok()) {
            $authyUser->authy_id = $response->id();
            $authyUser->save();
        }

        return $response;
    }

    public function sendTokenViaSms($authyId)
    {
        return $this->api
            ->requestSms($authyId, ['force' => 'true']);
    }

    public function sendTokenViaVoice($authyId)
    {
        return $this->api
            ->phoneCall($authyId, ['force' => 'true']);
    }

    public function verifyToken($authyId, $token, $addToSession = true)
    {
        $response = $this->api
            ->verifyToken($authyId, $token);

        if ($response->ok() && $addToSession) {
            $this->on2FASuccess('approved');
        }

        return $response;
    }

    private function on2FASuccess($authyStatus = 'approved')
    {
        session(['authy_status' => $authyStatus]);
    }
}
