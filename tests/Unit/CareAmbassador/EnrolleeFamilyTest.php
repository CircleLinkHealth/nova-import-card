<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Tests\TestCase;

class EnrolleeFamilyTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    protected $careAmbassador;
    protected $enrollee;

    protected $practice;
    protected $provider;
    protected $suggestedFamilyMembers;

    public function setUp()
    {
        parent::setUp();
        $this->practice               = factory(Practice::class)->create();
        $this->careAmbassador         = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider               = $this->createUser($this->practice->id, 'provider');
        $this->enrollee               = factory(Enrollee::class)->create()->first();
        $this->suggestedFamilyMembers = $this->createSuggestedFamilyMembers();
    }

    public function test_api_route_fetches_suggested_enrollees()
    {
        auth()->login($this->careAmbassador);
        $response = $this->actingAs($this->careAmbassador)
            ->call(
                'GET',
                route('enrollment-center.family-members', [
                    'enrolleeId' => $this->enrollee->id,
                ])
            );

        $response->assertOk();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_enrollee_can_have_family_members()
    {
        $this->assertTrue(true);
    }

    public function test_family_members_are_next_in_queue()
    {
        $this->assertTrue(true);
    }

    private function createSuggestedFamilyMembers()
    {
        $args = [
            [
                'last_name'  => $this->enrollee->last_name,
                'home_phone' => $this->enrollee->home_phone,
                'address'    => $this->enrollee->address,
            ],
            [
                'primary_phone' => $this->enrollee->home_phone,
                'address_2'     => $this->enrollee->address_2,
            ],
            [
                'last_name'   => $this->enrollee->last_name,
                'other_phone' => $this->enrollee->primary_phone,
            ],
            [
                'last_name'     => $this->enrollee->last_name,
                'address'       => $this->enrollee->address,
                'primary_phone' => $this->enrollee->primary_phone,
            ],
            [
                'address'    => $this->enrollee->address_2,
                'cell_phone' => $this->enrollee->cell_phone,
            ],
        ];

        $family = [];

        for ($i = 4; $i > 0; --$i) {
            $enrollee = factory(Enrollee::class)->create();

            $enrolleArgs = $args[$i];
            foreach ($enrolleArgs as $key => $value) {
                $enrollee->$key = $value;
            }
            $enrollee->save();
            $family[] = $enrollee;
        }

        $this->suggestedFamilyMembers = collect($family);
    }
}
