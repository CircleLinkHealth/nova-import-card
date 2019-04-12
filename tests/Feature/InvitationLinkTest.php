<?php

namespace Tests\Feature;

use App\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvitationLinkTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function data_are_saved_before_send_link_via_sms()
    {
        factory(PhoneNumber::class, 1)->create();
        $patient = factory(Patient::class)->create([
            'user_id' => 1,
        ]);

        $this->call('POST', route('createSendInvitationUrl'), [
            'id' => 1,
        ]);

        $this->assertDatabaseHas('invitation_links', [
            'patient_info_id' => $patient->id,
        ]);
    }

    /** @test */
   /* public function user_must_exist_in_db_to_be_invited()
    {

    }*/
}
