<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Services\Enrollment\EnrollableCallQueue;
use App\Traits\Tests\CareAmbassadorHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Tests\TestCase;

class CareAmbassadorPanelAPITest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CareAmbassadorHelpers;
    protected $admin;

    protected $careAmbassadorUser;
    protected $enrollee;
    protected $enrollees;
    protected $practice;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice           = factory(Practice::class)->create();
        $this->careAmbassadorUser = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider           = $this->createUser($this->practice->id, 'provider');
        $this->admin              = $this->createUser($this->practice->id, 'administrator');
        $this->enrollee           = factory(Enrollee::class)->create();
        $this->enrollees          = factory(Enrollee::class, 10)->create();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_assign_callback_to_ca_and_ca_can_see_callback_first_in_queue()
    {
        Enrollee::whereIn('id', $this->enrollees->pluck('id')->toArray())
            ->update([
                'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            ]);

        $testNote = 'test note';

        $this->actingAs($this->admin)->post(route('ca-director.assign-callback', [
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'enrollee_id'             => $this->enrollee->id,
            'callback_date'           => Carbon::now()->toDateString(),
            'callback_note'           => $testNote,
        ]))->assertOk();

        $this->enrollee = $this->enrollee->fresh();

        $this->assertEquals($this->enrollee->requested_callback->toDateString(), Carbon::today()->toDateString());
        $this->assertEquals($this->enrollee->care_ambassador_user_id, $this->careAmbassadorUser->id);
        $this->assertEquals($this->enrollee->callback_note, $testNote);

        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $this->enrollee->id);
    }

    public function test_ca_director_query_enrollables_fetches_suggestions()
    {
        //using id
        $data = $this->actingAs($this->admin)->get(route('enrollables.ca-director.search', [
            'enrollables' => (string) $this->enrollee->id,
        ]))->assertOk()
            ->getOriginalContent();

        $this->assertTrue(collect($data)->contains('id', $this->enrollee->id));

        //using first name
        $data = $this->actingAs($this->admin)->get(route('enrollables.ca-director.search', [
            'enrollables' => $this->enrollee->first_name,
        ]))->assertOk()
            ->getOriginalContent();

        $this->assertTrue(collect($data)->contains('id', $this->enrollee->id));

        //using last name
        $data = $this->actingAs($this->admin)->get(route('enrollables.ca-director.search', [
            'enrollables' => $this->enrollee->last_name,
        ]))->assertOk()
            ->getOriginalContent();

        $this->assertTrue(collect($data)->contains('id', $this->enrollee->id));
    }
}
