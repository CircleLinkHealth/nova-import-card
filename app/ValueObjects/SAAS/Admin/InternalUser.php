<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/28/2018
 * Time: 10:54 PM
 */

namespace App\ValueObjects\SAAS\Admin;

class InternalUser
{
    private $user;
    private $practices;
    private $role;

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
        if (! is_array($this->practices)) {
            return [$this->practices];
        }

        return $this->practices;
    }

    /**
     * @param mixed $practices
     */
    public function setPractices($practices): void
    {
        $this->practices = $practices;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role): void
    {
        $this->role = $role;
    }
}
