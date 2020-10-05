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
    use \CircleLinkHealth\Customer\Traits\UserHelpers;
    protected $careAmbassador;
    protected $enrollee;
    protected $nonSuggestedFamilyMembers;

    protected $practice;
    protected $provider;
    protected $suggestedFamilyMembers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice               = factory(Practice::class)->create();
        $this->careAmbassador         = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider               = $this->createUser($this->practice->id, 'provider');
        $this->enrollee               = factory(Enrollee::class)->create();
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

        //assert count of patients

        //assert fields are highlighted?
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
                'other_phone' => $this->enrollee->cell_phone,
            ],
            [
                'last_name'  => $this->enrollee->last_name,
                'address'    => $this->enrollee->address,
                'home_phone' => $this->enrollee->home_phone,
            ],
            [
                'address'    => $this->enrollee->address,
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

        $nonSuggested = [];
        for ($i = 4; $i > 0; --$i) {
            $nonSuggested[] = factory(Enrollee::class)->create();
        }

        $this->suggestedFamilyMembers    = collect($family);
        $this->nonSuggestedFamilyMembers = collect($nonSuggested);
    }
}
