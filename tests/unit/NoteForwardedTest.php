<?php

namespace Tests\Unit;

use App\Notifications\NoteForwarded;
use App\Practice;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Notification;
use Tests\TestCase;

class NoteForwardedTest extends TestCase
{
//    use DatabaseTransactions;
    use WithoutMiddleware;

    public function testExample()
    {
        $practice                           = Practice::find(8);
        $settings                           = $practice->cpmSettings();
        $settings->email_note_was_forwarded = true;
        $settings->efax_pdf_notes           = true;
        $settings->dm_pdf_notes             = true;
        $settings->save();

        $auth = User::find(357);
        auth()->login($auth);

        $note = User::findOrFail(874)->notes->first();

        Notification::fake();

        $note->forward(true, true);

        $recipients = collect();
        $recipients = $note->patient->care_team_receives_alerts;
        $recipients->push(User::find(948));

        $recipients->map(function ($user) {
            Notification::assertSentTo(
                $user,
                NoteForwarded::class
            );
        });
    }
}
