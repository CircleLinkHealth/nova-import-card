<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Services\SchedulerService;

class NotesTest extends CustomerTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_draft_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();
        $this->be($nurse);

        $this->createNote($patient->id);
    }

    public function test_edit_draft_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId);
    }

    public function test_save_draft_as_complete()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId, 'complete');
    }

    public function test_should_create_new_call_even_scheduled_call_for_another_nurse_from_successful_clinical_call_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurses   = $this->careCoach(2);
        $nurse1   = $nurses[0];
        $nurse2   = $nurses[1];

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'testing', $nurse1->id);

        $this->be($nurse2);

        $this->createNote($patient->id, null, 'complete', true);
        self::assertEquals(Call::SCHEDULED, $call->fresh()->status);
        self::assertTrue(Call::whereInboundCpmId($patient->id)
            ->whereOutboundCpmId($nurse2->id)
            ->where('status', Call::REACHED)
            ->exists());
    }

    public function test_should_create_new_call_from_successful_clinical_call_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();

        self::assertFalse(Call::whereInboundCpmId($patient->id)->exists());

        $this->be($nurse);

        $this->createNote($patient->id, null, 'complete', true);
        self::assertTrue(Call::whereInboundCpmId($patient->id)->exists());
    }

    public function test_should_mark_scheduled_call_successful_from_successful_clinical_call_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();

        /** @var SchedulerService $schedulerService */
        $schedulerService = app(SchedulerService::class);
        $call             = $schedulerService->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'testing', $nurse->id);

        $this->be($nurse);

        $this->createNote($patient->id, null, 'complete', true);
        self::assertEquals(Call::REACHED, $call->fresh()->status);
    }

    public function test_should_not_allow_edit_complete_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->careCoach();
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId, 'complete');

        $resp = $this->call(
            'POST',
            route('patient.note.store', ['patientId' => $patient->id]),
            [
                'note_id'    => $draftNoteId,
                'body'       => 'test-complete-edit',
                'patient_id' => $patient->id,
            ]
        );

        $resp->assertRedirect(url('/'));
        $this->assertEquals(
            'Cannot edit note. Please use create addendum to make corrections.',
            session()->get('errors')->getBag('default')->all()[0],
            'The expected message was not foudn in the session'
        );
    }

    private function createNote($patientId, $noteId = null, $status = 'draft', bool $withPhoneSession = false)
    {
        $isEditing = null !== $noteId;
        $route     = 'draft' === $status ? 'patient.note.store.draft' : 'patient.note.store';

        $args = [
            'status'     => $status,
            'note_id'    => $noteId,
            'body'       => $isEditing ? 'test-edit' : 'test',
            'patient_id' => $patientId,
        ];

        if ($withPhoneSession) {
            $args['phone']       = 1;
            $args['call_status'] = 'reached';
        }

        $resp = $this->call(
            'POST',
            route($route, ['patientId' => $patientId]),
            $args
        );

        if ('patient.note.store' === $route) {
            if ($withPhoneSession) {
                $resp->assertRedirect(route('manual.call.create', ['patientId' => $patientId]));
            } else {
                $resp->assertRedirect(route('patient.note.index', ['patientId' => $patientId]));
            }

            return;
        }

        $resp->assertOk();

        self::assertNull($resp->json('error'));

        $noteIdFromResp = $resp->json('note_id');
        if ($isEditing) {
            self::assertEquals($noteId, $noteIdFromResp);
        }
        $note = Note::find($noteIdFromResp);

        self::assertTrue($status === $note->status);
        self::assertTrue(($isEditing ? 'test-edit' : 'test') === $note->body);

        return $noteIdFromResp;
    }
}
