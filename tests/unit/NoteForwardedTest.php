<?php

namespace Tests\Unit;

use App\Notifications\NoteForwarded;
use App\Practice;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Notification;
use Tests\TestCase;
use Tests\Helpers\UserHelpers;

class NoteForwardedTest extends TestCase
{
    use DatabaseTransactions,
        WithoutMiddleware,
        UserHelpers;

    private $practice;
    private $admin, $user, $recipient;

    /**
     * Test that notes created for a patient are forwarded to care_team_members for that patient
     */
    public function test_it_sends_notifications()
    {
        $practice                           = $this->practice;
        $settings                           = $practice->cpmSettings();
        $settings->email_note_was_forwarded = true;
        $settings->efax_pdf_notes           = true;
        $settings->dm_pdf_notes             = true;
        $settings->save();

        $auth = $this->admin;
        auth()->login($auth);

        $this->user->notes()->create([
            'body' => '...',
            'author_id' => $this->admin->id,
            'isTCM' => 0,
            'did_medication_recon' => 0,
            'type' => 'general'
        ]);

        $note = $this->user->notes->first();

        Notification::fake();

        $note->forward(true, true);

        $recipients = collect();
        $recipients = $note->patient->care_team_receives_alerts;

        $this->assertEquals($note->patient->id, $this->user->id);

        $this->assertTrue($recipients->count() > 0);

        $recipients->map(function ($user) {
            Notification::assertSentTo(
                $user,
                NoteForwarded::class
            );
        });
    }

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();

        $this->admin = $this->createUser($this->practice->id, 'administrator');

        $this->user = $this->createUser($this->practice->id, 'participant');

        $this->recipient = $this->createUser($this->practice->id, 'provider');

        $this->user->careTeamMembers()->create([
            'member_user_id' => $this->recipient->id,
            'type' => 'billing_provider',
            'alert' => 1
        ]);

        $this->user->load('careTeamMembers');
    }
}
