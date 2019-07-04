<?php

namespace App\Providers;

use App\InvitationLink;
use App\User;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Carbon;

class AwvUserProvider implements UserProvider
{
    const LINK_EXPIRES_IN_DAYS = 14;

    /**
     * Create a new AWV user provider.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function retrieveById($identifier)
    {
        return User::where('id', $identifier)
                   ->first();
    }

    /**
     * Awv Login using name and dob does not support `Remember Me` token
     *
     * @param  mixed $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model $user
     * @param  string $token
     *
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $user->setRememberToken($token);

        $timestamps = $user->timestamps;

        $user->timestamps = false;

        $user->save();

        $user->timestamps = $timestamps;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        //$credentials = [ signed_token, name, dob ]

        $credentials = $this->sanitizeCredentialsArr($credentials);
        if ( ! $credentials) {
            return null;
        }

        $invitationLink = $this->getInvitationLink($credentials);
        if ( ! $invitationLink) {
            return null;
        }

        return $invitationLink->patientInfo->user;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        //$credentials = [ signed_token, name, dob ]

        $credentials = $this->sanitizeCredentialsArr($credentials);
        if ( ! $credentials) {
            return false;
        }

        $invitationLink = $this->getInvitationLink($credentials);
        if ( ! $invitationLink) {
            return false;
        }

        //check if link expired
        $urlUpdatedAt = $invitationLink->updated_at;
        $isExpiredUrl = $invitationLink->is_manually_expired;
        if ($isExpiredUrl || $urlUpdatedAt->diffInDays(Carbon::now()) > self::LINK_EXPIRES_IN_DAYS) {

            if ( ! $isExpiredUrl) {
                $invitationLink->is_manually_expired = true;
                $invitationLink->save();
            }

            return false;
        }

        return true;
    }

    private function sanitizeCredentialsArr(array $credentials): ?array
    {
        //$credentials = [ signed_token, name, dob ]

        if (empty($credentials) || count($credentials) !== 3) {
            return null;
        }

        if ( ! isset($credentials['signed_token']) ||
             ! isset($credentials['name']) ||
             ! isset($credentials['dob'])) {
            return null;
        }

        if ( ! ($credentials['dob'] instanceof Carbon)) {
            $credentials['dob'] = Carbon::parse($credentials['dob'])->startOfDay();
        }

        return $credentials;
    }

    private function getInvitationLink(array $credentials): ?InvitationLink
    {
        $token = $credentials['signed_token'];
        $name  = $credentials['name'];
        $dob   = $credentials['dob'];

        /** @var InvitationLink $invitationLink */
        $invitationLink = InvitationLink::with('patientInfo.user')
                                        ->where('link_token', $token)
                                        ->first();

        if ( ! $invitationLink) {
            return null;
        }

        $modelDob = $invitationLink->patientInfo->birth_date;
        if ( ! ($modelDob instanceof Carbon)) {
            $modelDob = Carbon::parse($modelDob)->startOfDay();
        }

        //check inputs
        if ( ! $dob->equalTo($modelDob) ||
             strcasecmp($invitationLink->patientInfo->user->display_name, $name) != 0) {
            return null;
        }

        return $invitationLink;
    }
}
