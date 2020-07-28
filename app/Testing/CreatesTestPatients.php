<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Testing;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Faker\Factory as Faker;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class CreatesTestPatients
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function create()
    {
        $repo     = new UserRepository();
        $role     = Role::byName('participant');
        $problems = CpmProblem::get();

        foreach ($this->data() as $patientData) {
            $this->deleteUsersIfExist($patientData['email']);

            $patientData['roles'] = [$role->id];
            $bag                  = new ParameterBag($patientData);

            $user = $repo->createNewUser($bag);

            $this->createAndAttachProblems($problems, $user, $patientData);

            $user->ccdMedications()->createMany($this->testMedications($patientData['medications']));

            $user->careTeamMembers()->create([
                'member_user_id' => $patientData['billing_provider_id'],
                'type'           => CarePerson::BILLING_PROVIDER,
            ]);

            $this->createSummaries($user);
        }
    }

    abstract protected function data();

    protected function getPracticeId()
    {
        $demoPractice = Practice::whereName('demo');

        return $demoPractice->exists()
            ? $demoPractice->first()->id
            : Practice::firstOrFail()->id;
    }

    protected function getProvider($id = null): User
    {
        if ($id && User::whereId($id)->exists()) {
            return User::find($id);
        }

        return User::ofType('provider')->firstOrFail();
    }

    /**
     * @param int $i
     *
     * Generate dummy medication names for users
     *
     * @return array
     */
    protected function testMedications($i = 25)
    {
        $medications = [];
        while ($i > 0) {
            $medications[] = ['name' => 'med'.' '.$i];
            --$i;
        }

        return $medications;
    }

    private function createAndAttachProblems($problems, User $user, $patientData)
    {
        $userProblems = in_array('all', $patientData['conditions'])
            ? $problems
            : $problems->whereIn('name', $patientData['conditions']);

        $ccdProblems = [];

        foreach ($userProblems as $problem) {
            $ccdProblems[] = [
                'is_monitored'   => 1,
                'name'           => $problem->name,
                'cpm_problem_id' => $problem->id,
            ];
        }
        $user->ccdProblems()->createMany($ccdProblems);
    }

    private function createSummaries($user)
    {
        $now = Carbon::now();

        $user->load('ccdProblems');

        $problem1Id = $user->ccdProblems->random()->id;
        $problem2Id = $user->ccdProblems->where('id', '!=', $problem1Id)->random()->id;

        for ($i = 9; $i > 0; --$i) {
            $date = $now->copy()->firstOfMonth()->subMonth($i);
            $user->patientSummaries()->updateOrCreate([
                'month_year' => $date,
            ], [
                'total_time'             => 1400,
                'ccm_time'               => 1400,
                'no_of_calls'            => 2,
                'no_of_successful_calls' => 1,
                'needs_qa'               => 1,
                'problem_1'              => $problem1Id,
                'problem_2'              => $problem2Id,
                'approved'               => 1,
                'actor_id'               => User::ofType('administrator')->first()->id,
                'created_at'             => $date->toDateTimeString(),
            ]);
        }
    }

    private function deleteUsersIfExist($email)
    {
        $users = User::whereEmail($email)->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                $user->patientSummaries()->delete();
                $user->forceDelete();
            }
        }
    }
}
