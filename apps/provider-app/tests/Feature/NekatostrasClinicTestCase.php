<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Tests\TestCase;

abstract class NekatostrasClinicTestCase extends TestCase
{
    private Practice $practice;

    protected function administrator()
    {
        return $this->queryUserOfType('administrator');
    }

    protected function careCoach()
    {
        return $this->queryUserOfType('care-center');
    }

    protected function patient()
    {
        return $this->queryUserOfType('participant');
    }

    protected function practice()
    {
        if ( ! isset($this->practice)) {
            $this->practice = \Cache::remember('_tests_nekatostras_practice', 2, function () {
                $p = Practice::whereName(\NekatostrasClinicSeeder::NEKATOSTRAS_PRACTICE)->first();

                if (is_null($p)) {
                    throw new \Exception('Data missing. Did you run `php artisan db:seed --class NekatostrasClinicSeeder`?');
                }

                return $p;
            });
        }

        return $this->practice;
    }

    protected function queryUserOfType(string $roleName)
    {
        return User::ofType($roleName)->ofPractice($this->practice());
    }

    protected function randomPatients(int $count)
    {
        return $this->queryUserOfType('participant')->take($count)->get()->all();
    }
}
