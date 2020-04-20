<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use App\Constants;
use Illuminate\Support\Facades\Cache;

trait HasCpmRoles
{
    protected $permsCacheOnObj = [];
    /**
     * This variable is used to cache roles on an object.
     *
     * @var array
     */
    protected $rolesCacheOnObj = [];

    public function clearObjectCache()
    {
        $this->rolesCacheOnObj = $this->permsCacheOnObj = [];
    }

    public function getCpmRolesCacheKey()
    {
        return "cpm_roles:user_id:$this->id";
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->getCachedRole('administrator');
    }

    /**
     * Returns whether the user is an administrator.
     *
     * @param bool $includeViewOnly
     */
    public function isCareAmbassador($includeViewOnly = true): bool
    {
        $arr = ['care-ambassador'];
        if ($includeViewOnly) {
            $arr[] = 'care-ambassador-view-only';
        }

        return $this->getCachedRole($arr);
    }

    /**
     * Returns whether the user is a Care Coach (AKA Care Center).
     * A Care Coach can be employed from CLH ['care-center']
     * or not ['care-center-external'].
     */
    public function isCareCoach(): bool
    {
        return $this->getCachedRole(['care-center', 'care-center-external']);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isEhrReportWriter(): bool
    {
        return $this->getCachedRole('ehr-report-writer');
    }

    /**
     * Returns whether the user is a participant.
     */
    public function isParticipant(): bool
    {
        return $this->getCachedRole('participant');
    }

    public function isPracticeStaff(): bool
    {
        return $this->getCachedRole(Constants::PRACTICE_STAFF_ROLE_NAMES);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isProvider(): bool
    {
        return $this->getCachedRole('provider');
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isSaasAdmin(): bool
    {
        return $this->getCachedRole('saas-admin');
    }

    /**
     * Returns whether the user is a Software Only user.
     */
    public function isSoftwareOnly(): bool
    {
        return $this->getCachedRole('software-only');
    }

    private function getCachedPermission($key)
    {
        return $this->hasPermission($key);
    }

    private function getCachedRole($key)
    {
        return $this->hasRole($key);
    }
}
