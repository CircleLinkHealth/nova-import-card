<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/16/18
 * Time: 3:09 PM
 */

namespace App\Services;


use App\Contracts\TwoFactorAuthenticationApi;
use App\User;
use Auth;
use Authy\AuthyApi;
use Authy\AuthyUser;
use Illuminate\Support\Facades\Session;
use Request;

class AuthyService
{
    /**
     * @var AuthyApi
     */
    private $api;

    public function __construct(AuthyApi $authyApi)
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
    public function createApprovalRequest(User $user)
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
    public function checkApprovalRequestStatus($approvalRequestUuid)
    {
        $response = $this->api
            ->getApprovalRequest($approvalRequestUuid);

        if ($response->ok()) {
            $approval_request = (array)$response->bodyvar('approval_request');
            session(['authy_status' => $approval_request['status']]);
        }

        return $response;
    }
}