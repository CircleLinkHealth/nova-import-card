<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Foundation\Testing\WithFaker;

class CustomerTestCase extends TestCase
{
    use UserHelpers;
    use WithFaker;
    /**
     * @var array|User
     */
    private $careCoach;

    /**
     * @var Enrollee
     */
    private $enrollee;

    /**
     * @var Location
     */
    private $location;
    private $medicalAssistant;
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
    /**
     * @var array|User
     */
    private $surveyOnly;

    /**
     * @return array|User
     */
    protected function careCoach(int $number = 1)
    {
        if ( ! $this->careCoach) {
            $this->careCoach = $this->createUsersOfType('care-center', $number);
        }

        return $this->careCoach;
    }

    protected function demoPractice()
    {
        return Practice::firstOrCreate(
            [
                'name' => 'demo',
            ],
            [
                'display_name'    => 'Demo',
                'saas_account_id' => 1,
                'outgoing_number' => '(678) 395-5261',
            ]
        );
    }

    protected function enrollee(int $number = 1)
    {
        if ( ! $this->enrollee) {
            $this->enrollee = factory(Enrollee::class)->create([
                'practice_id'             => $this->practice()->id,
                'dob'                     => \Carbon\Carbon::parse('1901-01-01'),
                'referring_provider_name' => 'Dr. Demo',
                'mrn'                     => mt_rand(100000, 999999),
                'email'                   => $this->faker->safeEmail,
            ]);
        }

        return $this->enrollee;
    }

    /**
     * @return Location
     */
    protected function location()
    {
        if ( ! $this->location) {
            $this->location = Location::where('practice_id', $this->practice()->id)->first();
            if ( ! $this->location) {
                $this->location = factory(Location::class)->create(['practice_id' => $this->practice()->id]);
            }
        }

        return $this->location;
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
     * @return array|User
     */
    protected function provider(int $number = 1)
    {
        if ( ! $this->provider) {
            $this->provider = $this->createUsersOfType('provider', $number);
        }

        return $this->provider;
    }

    /**
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
     * @return array|User
     */
    protected function surveyOnly(int $number = 1)
    {
        if ( ! $this->surveyOnly) {
            $this->surveyOnly = $this->createUsersOfType('survey-only', $number);
        }

        return $this->surveyOnly;
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
