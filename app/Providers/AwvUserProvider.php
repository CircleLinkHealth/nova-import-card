<?php

namespace App\Providers;

use App\InvitationLink;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Carbon;

class AwvUserProvider extends EloquentUserProvider
{
    const LINK_EXPIRES_IN_DAYS = 14;

    /**
     * Create a new AWV user provider.
     *
     * @param HasherContract $hasher
     * @param $model
     */
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
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

        if ( ! isset($credentials['signed_token'])) {
            return parent::retrieveByCredentials($credentials);
        }

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

        if ( ! isset($credentials['signed_token'])) {
            return parent::validateCredentials($user, $credentials);
        }

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
