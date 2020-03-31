<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Contracts;

use Authy\AuthyResponse;
use Authy\AuthyUser;

interface AuthyApiable
{
    const QR_DEFAULT_SIZE = 300;

    /**
     * Create a new approval request for a user.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options
     * @param mixed $message
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#create-approvalrequest
     */
    public function createApprovalRequest($authy_id, $message, $opts = []): AuthyResponse;

    /**
     * Deletes an user.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function deleteUser($authy_id): AuthyResponse;

    /**
     * Check the status of an approval request.
     *
     * @param string $request_uuid The UUID of the approval request you want to check
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#check-approvalrequest-status
     */
    public function getApprovalRequest($request_uuid): AuthyResponse;

    /**
     * Cellphone call, usually used with SMS Token issues or if no smartphone is available.
     * This function needs the app to be on Starter Plan (free) or higher.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function phoneCall($authy_id, $opts = []): AuthyResponse;

    /**
     * Phone information. Gather additional information about user's phone.
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function phoneInfo($phone_number, $country_code): AuthyResponse;

    /**
     * Phone verification check. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $verification_code The verification code entered by the user to be checked
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationCheck($phone_number, $country_code, $verification_code): AuthyResponse;

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $via The method the token will be sent to user (sms or call)
     * @param int $code_length The length of the verifcation code to be sent to the user
     * @param mixed|null $locale
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart(
        $phone_number,
        $country_code,
        $via = 'sms',
        $code_length = 4,
        $locale = null
    ): AuthyResponse;

    /**
     * Register a user.
     *
     * @param string $email New user's email
     * @param string $cellphone New user's cellphone
     * @param int $country_code New user's country code. defaults to USA(1)
     * @param bool $send_install_link
     *
     * @return AuthyUser the new registered user
     */
    public function registerUser($email, $cellphone, $country_code = 1, $send_install_link = true): AuthyResponse;

    /**
     * Request a valid token via SMS.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function requestSms($authy_id, $opts = []): AuthyResponse;

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
    public function requestQrCode($auth_id, $opts = []): AuthyResponse;

    /**
     * Gets user status.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function userStatus($authy_id): AuthyResponse;

    /**
     * Verify a given token.
     *
     * @param string $authy_id User's id stored in your database
     * @param string $token The token entered by the user
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function verifyToken($authy_id, $token, $opts = []): AuthyResponse;
}
