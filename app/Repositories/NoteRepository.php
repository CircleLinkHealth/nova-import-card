<?php
namespace App\Repositories;


use App\Note;
use Exception;

class NoteRepository
{
    public function model()
    {
        return app(Note::class);
    }
    
    public function count() {
        return $this->model()->count();
    }

    public function exists($id) {
        return !!$this->model()->find($id);
    }

    public function patientNotes($userId) {
        return $this->model()->orderBy('id', 'desc')->where([
            'patient_id' => $userId
        ])->paginate();
    }

    public function add(Note $note) {
        if ($note && $note->patient_id && $note->author_id && $note->body && $note->type) {
            $note->save();
            return $note;
        }
        else {
            if (!$note) throw new Exception('invalid $note');
            else if (!$note->patient_id) throw new Exception('invalid $note->patient_id');
            else if (!$note->author_id) throw new Exception('invalid $note->author_id');
            else if (!$note->body) throw new Exception('invalid $note->body');
            else if (!$note->type) throw new Exception('invalid $note->type');
            else throw new Exception('invalid parameters');
        }
    }

    public function edit(Note $note) {
        if ($note && $note->id && $this->exists($note->id)) {
            $notes = $this->model()->where([ 'id' => $note->id ]);
            $notes->update([ 'body' => $note->body, 'isTCM' => $note->isTCM ?? 0, 'did_medication_recon' => $note->did_medication_recon ]);
            return $notes->first();
        }
        else return null;
    }
}