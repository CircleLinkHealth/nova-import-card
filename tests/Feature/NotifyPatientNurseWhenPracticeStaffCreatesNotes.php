<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\SharedModels\Entities\Note;

class NotifyPatientNurseWhenPracticeStaffCreatesNotes extends CustomerTestCase
{
    public function getFakeCreateNoteParams(User $author)
    {
        return [
            'ccm_status'             => Patient::ENROLLED,
            'withdrawn_reason'       => '',
            'withdrawn_reason_other' => '',
            'general_comment'        => 'Hello hello  hello  hello  hello  hello  hello  hello',
            'type'                   => 'CCM Welcome Call',
            'performed_at'           => now()->toDateTimeString(),
            'tcm'                    => 'hospital',
            'email-subject'          => '',
            'patient-email-body'     => '',
            'summary'                => '',
            'body'                   => 'Test note',
            'patient_id'             => $this->patient()->id,
            'logger_id'              => $author->id,
            'author_id'              => $author->id,
            'programId'              => $this->practice()->id,
            'task_status'            => '',
        ];
    }

    public function test_it_does_not_send_notification_when_a_non_member_of_practice_staff_writes_note()
    {
        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);

        Notification::fake();

        $note = Note::create($this->getFakeCreateNoteParams($this->superadmin()));

        Notification::assertNothingSent();
    }

    public function test_it_sends_notification_when_practice_staff_writes_note()
    {
        app(NurseFinderEloquentRepository::class)->assign($this->patient()->id, $this->careCoach()->id);

        Notification::fake();

        $note = Note::create($this->getFakeCreateNoteParams($this->medicalAssistant()));

        Notification::assertSentTo($this->careCoach(), PracticeStaffCreatedNote::class);
    }
}
