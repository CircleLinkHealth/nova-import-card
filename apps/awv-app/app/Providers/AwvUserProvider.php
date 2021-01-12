<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\InvitationLink;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Carbon;

class AwvUserProvider extends EloquentUserProvider
{
    const LINK_EXPIRES_IN_DAYS = 14;

    /**
     * Create a new AWV user provider.
     *
     * @param $model
     */
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        //$credentials = [ signed_token, name, dob ]

        if ( ! $this->isPatientLogin($credentials)) {
            return parent::retrieveByCredentials($credentials);
        }

        $credentials = $this->sanitizeCredentialsArr($credentials);
        if ( ! $credentials) {
            return;
        }

        $invitationLink = $this->getInvitationLink($credentials);
        if ( ! $invitationLink) {
            return;
        }

        return $invitationLink->patientInfo->user;
    }

    /**
     * Validate a user against the given credentials.
     *
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        //$credentials = [ signed_token, name, dob ]

        if ( ! $this->isPatientLogin($credentials)) {
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
             0 != strcasecmp($invitationLink->patientInfo->user->display_name, $name)) {
            return null;
        }

        return $invitationLink;
    }

    private function isPatientLogin(array $credentials): bool
    {
        return isset($credentials['signed_token']);
    }

    private function sanitizeCredentialsArr(array $credentials): ?array
    {
        //$credentials = [ signed_token, name, dob ]

        if (empty($credentials) || 3 !== count($credentials)) {
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
}
