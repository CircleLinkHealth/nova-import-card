<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/16/18
 * Time: 3:09 PM
 */

namespace App\Services;


use App\Contracts\AuthyApiable;
use App\User;
use Authy\AuthyUser;

class AuthyService
{
    /**
     * @var AuthyApiable
     */
    private $api;

    /**
     * AuthyService constructor.
     *
     * @param AuthyApiable $authyApi
     */
    public function __construct(AuthyApiable $authyApi)
    {
        $this->api = $authyApi;
    }

    /**
     * Register a User with Authy
     *
     * @param User $user
     *
     * @return AuthyUser
     */
    public function register(User $user)
    {
        $authyUser = $this->api
            ->registerUser($user->email, $user->phone_number, $user->country_code);

        if ($authyUser->ok()) {
            $user->authy_id = $authyUser->id();
            $user->save();
        }

        return $authyUser;
    }

    /**
     * Create approval request.
     *
     * @param User $user
     *
     * @return \Authy\AuthyResponse
     */
    public function createOneTouchRequest(User $user)
    {
        $response = $this
            ->api
            ->createApprovalRequest(
                $user->authy_id,
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
            $approval_request = (array)$response->bodyvar('approval_request');
            session(['approval_request_uuid' => $approval_request['uuid']]);
        }

        return $response;
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
            $approval_request = (array)$response->bodyvar('approval_request');
            $this->on2FASuccess($approval_request['status']);
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

    public function verifyToken($authyId, $token)
    {
        $response = $this->api
            ->verifyToken($authyId, $token);

        if ($response->ok()) {
            $this->on2FASuccess();
        }

        return $response;
    }

    private function on2FASuccess($authyStatus = 'approved')
    {
        session(['authy_status' => $authyStatus]);
    }
}