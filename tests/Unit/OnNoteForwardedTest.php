<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\NoteForwarded;
use App\Services\NoteService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\CustomerTestCase;

class OnNoteForwardedTest extends CustomerTestCase
{
    use UserHelpers;
    use WithoutMiddleware;
    protected $admin;
    protected $nurse;
    protected $patient;

    protected $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $carePerson = $this->patient()->careTeamMembers()->create([
            'member_user_id' => $this->careCoach()->id,
            'type'           => 'member',
            'alert'          => 1,
        ]);

        $this->patient()->notes()->create([
            'author_id'    => $this->careCoach()->id,
            'body'         => 'test',
            'logger_id'    => $this->careCoach()->id,
            'performed_at' => Carbon::now(),
            'type'         => 'Patient Consented',
        ]);
    }

    /**
     * Test that notes created for a patient are forwarded to care_team_members for that patient.
     */
    public function test_it_sends_notifications()
    {
        $practice                           = $this->practice();
        $settings                           = $practice->cpmSettings();
        $settings->email_note_was_forwarded = true;
        $settings->efax_pdf_notes           = true;
        $settings->dm_pdf_notes             = true;
        $settings->save();

        //admin
        $auth = $this->superadmin();
        auth()->login($auth);

        $note = $this->patient()->notes()->first();

        Notification::fake();

        $note->forward(true, true);

        $recipients = collect();
        $recipients = $note->patient->getCareTeamReceivesAlerts();
        //care center
        $recipients->push($this->careCoach());

        $recipients->map(function ($user) {
            Notification::assertSentTo(
                $user,
                NoteForwarded::class
            );
        });
    }

    public function test_practice_has_notes_notifications_enabled_method()
    {
        $service = app(NoteService::class);

        $settings                           = $this->practice()->cpmSettings();
        $settings->email_note_was_forwarded = true;
        $settings->efax_pdf_notes           = true;
        $settings->dm_pdf_notes             = true;
        $settings->save();

        $this->assertTrue($service->practiceHasNotesNotificationsEnabled($this->refreshSettings()));

        $settings->email_note_was_forwarded = false;
        $settings->efax_pdf_notes           = false;
        $settings->dm_pdf_notes             = false;
        $settings->save();

        $this->assertFalse($service->practiceHasNotesNotificationsEnabled($this->refreshSettings()));

        $settings->dm_pdf_notes = true;
        $settings->save();

        $this->assertTrue($service->practiceHasNotesNotificationsEnabled($this->refreshSettings()));
    }

    private function refreshSettings()
    {
        return $this->practice()->load('settings');
    }
}
