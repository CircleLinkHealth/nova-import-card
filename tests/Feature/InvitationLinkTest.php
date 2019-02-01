<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvitationLinkTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function a_provider_can_send_invite_sms()
    {
        $this->withoutExceptionHandling();

        $attributes  = [
            'awv_patient_id' => $this->faker->numberBetween(0, 40),
            'survey_id'      => $this->faker->randomNumber(),
            'link_token'     => $this->faker->url,
            'is_expired'     => false,
        ];

        $phoneNumber = [
            'number' =>  $this->faker->phoneNumber,
        ];

        $this->post(route('createSendUrl', $phoneNumber) )
        ->assertStatus(201);
        //$this->assertDatabaseHas('invitation_links', $attributes);
    }

    /** @test */
    public function invitation_link_table_requires_patient_user_id()
    {
        $attributes = factory('App\InvitationLink')->raw(['patient_user_id' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('patient_user_id');
    }

    /** @test */
    public function invitation_link_table_requires_patient_name()
    {
        $attributes = factory('App\InvitationLink')->raw(['patient_name' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('patient_name');
    }

    /** @test */
    public function invitation_link_table_requires_birth_date()
    {
        $attributes = factory('App\InvitationLink')->raw(['birth_date' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('birth_date');
    }

    /** @test */
    public function invitation_link_table_requires_survey_id()
    {
        $attributes = factory('App\InvitationLink')->raw(['survey_id' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('survey_id');
    }

    /** @test */
    public function invitation_link_table_requires_link_token()
    {
        $attributes = factory('App\InvitationLink')->raw(['link_token' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('link_token');
    }

    /** @test */
    public function invitation_link_table_requires_is_expired()
    {
        $attributes = factory('App\InvitationLink')->raw(['is_expired' => '']);

        $this->post('/send.sms', $attributes)->assertSessionHasErrors('is_expired');
    }
}
