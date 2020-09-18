<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\DTO;

class InternalUser
{
    private $practices;
    private $role;
    private $user;

    public function __construct($user, $practiceIds, $roleId)
    {
        $this->user      = $user;
        $this->practices = $practiceIds;
        $this->role      = $roleId;
    }

    /**
     * @return mixed
     */
    public function getPractices()
    {
        if ( ! is_array($this->practices)) {
            return [$this->practices];
        }

        return $this->practices;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $practices
     */
    public function setPractices($practices): void
    {
        $this->practices = $practices;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role): void
    {
        $this->role = $role;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }
}
