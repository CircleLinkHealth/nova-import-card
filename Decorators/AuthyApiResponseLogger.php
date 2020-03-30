<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Decorators;

use Authy\AuthyApi;
use Authy\AuthyResponse;
use Authy\AuthyUser;
use Carbon\Carbon;
use CircleLinkHealth\TwoFA\Contracts\AuthyApiable;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use Storage;

class AuthyResponseLogger implements AuthyApiable
{
    /**
     * @var AuthyApi
     */
    private $authyApi;

    /**
     * Http Client for QR code generation.
     * AuthyApi does not have a helper for this yet.
     *
     * @var Client
     */
    private $httpClient;

    /**
     * AuthyResponseLogger constructor.
     *
     * @param AuthyApi $authyApi
     * @param Client $httpClient
     */
    public function __construct(AuthyApi $authyApi, Client $httpClient)
    {
        $this->authyApi = $authyApi;
        $this->httpClient = $httpClient;
    }

    /**
     * Create a new approval request for a user.
     *
     * @param string $authy_id User's id stored in your database
     * @param $message
     * @param array $opts Array of options
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#create-approvalrequest
     */
    public function createApprovalRequest($authy_id, $message, $opts = []): AuthyResponse
    {
        $response = $this->authyApi->createApprovalRequest($authy_id, $message, $opts);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'message', 'opts', 'fnName'));

        return $response;
    }

    /**
     * Deletes an user.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function deleteUser($authy_id): AuthyResponse
    {
        $response = $this->authyApi->deleteUser($authy_id);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'fnName'));

        return $response;
    }

    /**
     * Check the status of an approval request.
     *
     * @param string $request_uuid The UUID of the approval request you want to check
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#check-approvalrequest-status
     */
    public function getApprovalRequest($request_uuid): AuthyResponse
    {
        $response = $this->authyApi->getApprovalRequest($request_uuid);

        $fnName = __FUNCTION__;

        $this->log($response, compact('request_uuid', 'fnName'));

        return $response;
    }

    /**
     * Cellphone call, usually used with SMS Token issues or if no smartphone is available.
     * This function needs the app to be on Starter Plan (free) or higher.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function phoneCall($authy_id, $opts = []): AuthyResponse
    {
        $response = $this->authyApi->phoneCall($authy_id, $opts);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'opts', 'fnName'));

        return $response;
    }

    /**
     * Phone information. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function phoneInfo($phone_number, $country_code): AuthyResponse
    {
        $response = $this->authyApi->phoneInfo($phone_number, $country_code);

        $fnName = __FUNCTION__;

        $this->log($response, compact('phone_number', 'country_code', 'fnName'));

        return $response;
    }

    /**
     * Phone verification check. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $verification_code The verification code entered by the user to be checked
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationCheck($phone_number, $country_code, $verification_code): AuthyResponse
    {
        $response = $this->authyApi->phoneVerificationCheck($phone_number, $country_code, $verification_code);

        $fnName = __FUNCTION__;

        $this->log($response, compact('phone_number', 'country_code', 'verification_code', 'fnName'));

        return $response;
    }

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $via The method the token will be sent to user (sms or call)
     * @param int $code_length
     * @param null $locale
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart(
        $phone_number,
        $country_code,
        $via = 'sms',
        $code_length = 4,
        $locale = null
    ): AuthyResponse {
        $response = $this->authyApi->phoneVerificationStart($phone_number, $country_code, $via, $code_length, $locale);

        $fnName = __FUNCTION__;

        $this->log($response, compact('phone_number', 'country_code', 'via', 'locale', 'fnName'));

        return $response;
    }

    /**
     * Register a user.
     *
     * @param string $email New user's email
     * @param string $cellphone New user's cellphone
     * @param int $country_code New user's country code. defaults to USA(1)
     * @param mixed $send_install_link
     *
     * @return AuthyUser the new registered user
     */
    public function registerUser($email, $cellphone, $country_code = 1, $send_install_link = true): AuthyResponse
    {
        $response = $this->authyApi->registerUser($email, $cellphone, $country_code, $send_install_link);

        $fnName = __FUNCTION__;

        $this->log($response, compact('email', 'cellphone', 'country_code', 'fnName'));

        return $response;
    }

    /**
     * Request a valid token via SMS.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function requestSms($authy_id, $opts = []): AuthyResponse
    {
        $response = $this->authyApi->requestSms($authy_id, $opts);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'opts', 'fnName'));

        return $response;
    }

    /**
     * Request a link to a QR code to support other authenticator apps.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example:
     *              [
     *                  "label" => "AppName(myuser@example.com)",
     *                  "qr_size" => 300
     *              ]
     *
     * @return AuthyResponse the server's response
     */
    public function requestQrCode($authy_id, $opts = []): AuthyResponse
    {
        $authy_id = urlencode($authy_id);

        if (isset($opts['label'])) {
            $opts['label'] = $authy_id;
        }
        if (isset($opts['label'])) {
            $opts['qr_size'] = AuthyApiable::QR_DEFAULT_SIZE;
        }

        $resp = $this->httpClient->post("protected/json/users/{$authy_id}/secret", $opts);

        return new AuthyResponse($resp);
    }

    /**
     * Gets user status.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function userStatus($authy_id): AuthyResponse
    {
        $response = $this->authyApi->userStatus($authy_id);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'fnName'));

        return $response;
    }

    /**
     * Verify a given token.
     *
     * @param string $authy_id User's id stored in your database
     * @param string $token The token entered by the user
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function verifyToken($authy_id, $token, $opts = []): AuthyResponse
    {
        $response = $this->authyApi->verifyToken($authy_id, $token, $opts);

        $fnName = __FUNCTION__;

        $this->log($response, compact('authy_id', 'token', 'opts', 'fnName'));

        return $response;
    }

    private function log($response, $arguments = [])
    {
        if ( ! is_a($response, AuthyResponse::class) || ! $response) {
            return null;
        }

        $args = $this->toArray($response, $arguments);

        $date = Carbon::now()->toDateString();

        Storage::disk('media')
               ->append("logs/authy/authy-${date}.log", json_encode($args));
    }

    private function toArray(AuthyResponse $response, $arguments = [])
    {
        return [
            'created_at'   => Carbon::now()->toAtomString(),
            'message'      => $response->bodyvar('message'),
            'auth_user_id' => auth()->id(),
            'success'      => $response->ok(),
            'errors'       => $response->errors(),
            'error_code'   => $response->bodyvar('error_code') ?? '',
            'response_id'  => $response->id(),
            'args'         => $arguments,
            'route'        => [
                'route'  => Route::current(),
                'name'   => Route::currentRouteName(),
                'action' => Route::currentRouteAction(),
            ],
        ];
    }
}
