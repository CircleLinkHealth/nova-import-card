<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\Family;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\CustomerTestCase;

class FamilyMembersTest extends CustomerTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_family_members()
    {
        $family = Family::create();

        /** @var Collection $patients */
        $patients = collect($this->patient(2));
        $patients->each(function (User $user) use ($family) {
            $user->patientInfo->family_id = $family->id;
            $user->patientInfo->save();
        });

        $patientId = $patients->first()->id;
        $route     = route('family.get', ['patientId' => $patientId]);
        $resp      = $this->be($this->careCoach())->json('GET', $route);
        $resp->assertOk();
        $members = $resp->json('members');
        self::assertEquals(1, sizeof($members));
    }
}
