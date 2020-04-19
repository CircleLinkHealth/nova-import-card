<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Tests\Helpers\CustomerTestCaseHelper;

class CustomerTestCase extends TestCase
{
    use UserHelpers;
    /**
     * @var array|User
     */
    private $careCoach;

    /**
     * @var Location
     */
    private $location;
    /**
     * @var array|User
     */
    private $patient;
    /**
     * @var Practice
     */
    private $practice;
    /**
     * @var array|User
     */
    private $provider;
    /**
     * @var array|User
     */
    private $superadmin;
    private $medicalAssistant;
    
    /**
     * @param int $number
     *
     * @return array|User
     */
    protected function careCoach(int $number = 1)
    {
        if ( ! $this->careCoach) {
            $this->careCoach = $this->createUsersOfType('care-center', $number);
        }

        return $this->careCoach;
    }
    
    /**
     * @param int $number
     *
     * @return array|User
     */
    protected function superadmin(int $number = 1)
    {
        if ( ! $this->superadmin) {
            $this->superadmin = $this->createUsersOfType('administrator', $number);
        }
        
        return $this->superadmin;
    }

    /**
     * @return Location
     */
    protected function location()
    {
        if ( ! $this->location) {
            $this->location = Location::firstOrCreate(
                [
                    'practice_id' => $this->practice()->id,
                ]
            );
        }

        return $this->location;
    }
    
    /**
     * @param int $number
     *
     * @return array|User
     */
    protected function patient(int $number = 1)
    {
        if ( ! $this->patient) {
            $this->patient = $this->createUsersOfType('participant', $number);
        }

        return $this->patient;
    }

    /**
     * @return Practice
     */
    protected function practice()
    {
        if ( ! $this->practice) {
            $this->practice = factory(Practice::class)->create();
            $this->location();
        }

        return $this->practice;
    }
    
    /**
     * @param int $number
     *
     * @return array|User
     */
    protected function provider(int $number = 1)
    {
        if ( ! $this->provider) {
            $this->provider = $this->createUsersOfType('provider', $number);
        }

        return $this->provider;
    }
    
    protected function medicalAssistant(int $number = 1)
    {
        if ( ! $this->medicalAssistant) {
            $this->medicalAssistant = $this->createUsersOfType('med_assistant', $number);
        }
        
        return $this->medicalAssistant;
    }

    /**
     * @return array|User
     */
    private function createUsersOfType(string $roleName, int $number = 1)
    {
        if ($number > 1) {
            for ($i = 1; $i <= $number; ++$i) {
                $users[] = $this->createUser($this->practice()->id, $roleName);
            }
        } else {
            $users = $this->createUser($this->practice()->id, $roleName);
        }

        return $users;
    }
}
