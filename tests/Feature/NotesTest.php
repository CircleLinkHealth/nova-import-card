<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Http\Controllers\NotesController;
use App\Note;
use Illuminate\Testing\TestResponse;
use Tests\CustomerTestCase;
use Tests\Helpers\MakesSafeRequests;

class NotesTest extends CustomerTestCase
{
    use MakesSafeRequests;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_draft_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->createUser($practice->id, 'care-center');
        $this->be($nurse);

        $this->createNote($patient->id);
    }

    public function test_edit_draft_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->createUser($practice->id, 'care-center');
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId);
    }

    public function test_save_draft_as_complete()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->createUser($practice->id, 'care-center');
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId, 'complete');
    }

    public function test_should_not_allow_edit_complete_note()
    {
        $practice = $this->practice();
        $patient  = $this->patient();
        $nurse    = $this->createUser($practice->id, 'care-center');
        $this->be($nurse);

        $draftNoteId = $this->createNote($patient->id);
        $this->createNote($patient->id, $draftNoteId, 'complete');

        /** @var NotesController $controller */
        $controller = app(NotesController::class);

        $req = $this->safeRequest(
            route('patient.note.store', ['patientId' => $patient->id]),
            'POST',
            [
                'note_id'    => $draftNoteId,
                'body'       => 'test-complete-edit',
                'patient_id' => $patient->id,
            ]
        );

        $resp = TestResponse::fromBaseResponse($controller->storeDraft($req, $patient->id));
        $resp->assertOk();
        self::assertTrue(null !== $resp->json('error'));
    }

    private function createNote($patientId, $noteId = null, $status = 'draft')
    {
        $isEditing = null !== $noteId;
        /** @var NotesController $controller */
        $controller = app(NotesController::class);

        $req = $this->safeRequest(
            route('draft' === $status ? 'patient.note.store.draft' : 'patient.note.store', ['patientId' => $patientId]),
            'POST',
            [
                'status'     => $status,
                'note_id'    => $noteId,
                'body'       => $isEditing ? 'test-edit' : 'test',
                'patient_id' => $patientId,
            ]
        );

        $resp = TestResponse::fromBaseResponse($controller->storeDraft($req, $patientId));
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
