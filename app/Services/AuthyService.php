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
use Authy\AuthyUser as AuthyApiUser;
use App\AuthyUser;

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
     * @param AuthyUser $authyUser
     * @param User $user
     *
     * @return AuthyApiUser
     */
    public function register(AuthyUser $authyUser, User $user)
    {
        $response = $this->api
            ->registerUser($user->email, $authyUser->phone_number, $authyUser->country_code, $authyUser->authy_method == 'app');

        if ($response->ok()) {
            $authyUser->authy_id = $response->id();
            $authyUser->save();
        }

        return $response;
    }

    /**
     * Create approval request.
     *
     * @param AuthyUser $authyUser
     * @param User $user
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