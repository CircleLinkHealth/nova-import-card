<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\NoteForwarded;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NoteForwardedTest extends TestCase
{
    use
        \App\Traits\Tests\UserHelpers;
    use WithoutMiddleware;
    protected $admin;
    protected $nurse;
    protected $patient;

    protected $practice;

    protected function setUp()
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->admin    = $this->createUser($this->practice->id, 'administrator');

        $this->nurse = $this->createUser($this->practice->id, 'care-center');

        $carePerson = $this->patient->careTeamMembers()->create([
            'member_user_id' => $this->nurse->id,
            'type'           => 'member',
            'alert'          => 1,
        ]);

        $this->patient->notes()->create([
            'author_id'    => $this->nurse->id,
            'body'         => 'test',
            'logger_id'    => $this->nurse->id,
            'performed_at' => Carbon::now(),
            'type'         => 'Patient Consented',
        ]);
    }

    /**
     * Test that notes created for a patient are forwarded to care_team_members for that patient.
     */
    public function test_it_sends_notifications()
    {
        $practice                           = $this->practice;
        $settings                           = $practice->cpmSettings();
        $settings->email_note_was_forwarded = true;
        $settings->efax_pdf_notes           = true;
        $settings->dm_pdf_notes             = true;
        $settings->save();

        //admin
        $auth = $this->admin;
        auth()->login($auth);

        //participant
        $note = $this->patient->notes()->first();

        Notification::fake();

        $note->forward(true, true);

        $recipients = collect();
        $recipients = $note->patient->getCareTeamReceivesAlerts();
        //care center
        $recipients->push($this->nurse);

        $recipients->map(function ($user) {
            Notification::assertSentTo(
                $user,
                NoteForwarded::class
            );
        });
    }
}
