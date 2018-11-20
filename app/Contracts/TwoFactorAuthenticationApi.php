<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/16/18
 * Time: 3:12 PM
 */

namespace App\Contracts;


interface TwoFactorAuthenticationApi
{
    /**
     * Register a user.
     *
     * @param  string $email New user's email
     * @param  string $cellphone New user's cellphone
     * @param  int $country_code New user's country code. defaults to USA(1)
     *
     * @return AuthyUser the new registered user
     */
    public function registerUser($email, $cellphone, $country_code = 1, $send_install_link = true);

    /**
     * Verify a given token.
     *
     * @param string $authy_id User's id stored in your database
     * @param string $token The token entered by the user
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function verifyToken($authy_id, $token, $opts = []);

    /**
     * Request a valid token via SMS.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function requestSms($authy_id, $opts = []);

    /**
     * Cellphone call, usually used with SMS Token issues or if no smartphone is available.
     * This function needs the app to be on Starter Plan (free) or higher.
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options, for example: array("force" => "true")
     *
     * @return AuthyResponse the server response
     */
    public function phoneCall($authy_id, $opts = []);

    /**
     * Deletes an user.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function deleteUser($authy_id);

    /**
     * Gets user status.
     *
     * @param string $authy_id User's id stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function userStatus($authy_id);

    /**
     * Starts phone verification. (Sends token to user via sms or call).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $via The method the token will be sent to user (sms or call)
     * @param int $code_length The length of the verifcation code to be sent to the user
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationStart(
        $phone_number,
        $country_code,
        $via = 'sms',
        $code_length = 4,
        $locale = null
    );

    /**
     * Phone verification check. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     * @param string $verification_code The verification code entered by the user to be checked
     *
     * @return AuthyResponse the server response
     */
    public function phoneVerificationCheck($phone_number, $country_code, $verification_code);

    /**
     * Phone information. (Checks whether the token entered by the user is valid or not).
     *
     * @param string $phone_number User's phone_number stored in your database
     * @param string $country_code User's phone country code stored in your database
     *
     * @return AuthyResponse the server response
     */
    public function phoneInfo($phone_number, $country_code);

    /**
     * Create a new approval request for a user
     *
     * @param string $authy_id User's id stored in your database
     * @param array $opts Array of options
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#create-approvalrequest
     */
    public function createApprovalRequest($authy_id, $message, $opts = []);

    /**
     * Check the status of an approval request
     *
     * @param string $request_uuid The UUID of the approval request you want to check
     *
     * @return AuthyResponse
     *
     * @see http://docs.authy.com/onetouch.html#check-approvalrequest-status
     */
    public function getApprovalRequest($request_uuid);
}