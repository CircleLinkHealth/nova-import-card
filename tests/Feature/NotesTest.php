<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Note;
use Tests\CustomerTestCase;

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

    private function createNote($patientId, $noteId = null, $status = 'draft')
    {
        $isEditing = null !== $noteId;
        $route     = 'draft' === $status ? 'patient.note.store.draft' : 'patient.note.store';

        $resp = $this->call(
            'POST',
            route($route, ['patientId' => $patientId]),
            [
                'status'     => $status,
                'note_id'    => $noteId,
                'body'       => $isEditing ? 'test-edit' : 'test',
                'patient_id' => $patientId,
            ]
        );

        if ('patient.note.store' === $route) {
            $resp->assertRedirect(route('patient.note.index', ['patientId' => $patientId]));
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
