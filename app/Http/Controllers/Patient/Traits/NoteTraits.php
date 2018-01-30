<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait NoteTraits
{
    public function getNotes($userId, Request $request) {
        if ($userId) {
            $type = $request->input('type');
            return $this->noteService->patientNotes($userId, $type);
        }
        else return $this->badRequest('"userId" is important');
    }
    
    public function addNote($userId, Request $request) {
        $body = $request->input('body');
        $author_id = auth()->user()->id;
        $type = $request->input('type');
        $isTCM = $request->input('isTCM') ?? 0;
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && $body && $author_id) {
            return $this->noteService->add($userId, $author_id, $body, $type, $isTCM, $did_medication_recon);
        }
        else return $this->badRequest('"userId" and "body" and "author_id" are important');
    }
    
    public function editNote($userId, $id, Request $request) {
        $body = $request->input('body');
        $author_id = auth()->user()->id;
        $isTCM = $request->input('isTCM') ?? 0;
        $type = $request->input('type');
        $did_medication_recon = $request->input('did_medication_recon') ?? 0;
        if ($userId && ($id || $type) && $author_id) {
            return $this->noteService->editPatientNote($id, $userId, $author_id, $body, $isTCM, $did_medication_recon, $type);
        }
        else return $this->badRequest('"userId", "author_id" and "noteId" are is important');
    }
}
