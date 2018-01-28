<?php
namespace App\Repositories;


use App\Note;
use Exception;
use Carbon\Carbon;

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

    public function patientNotes($userId, $type = null) {
        $query = $this->model()->orderBy('id', 'desc')->where([
            'patient_id' => $userId
        ]);
        if ($type) {
            $query = $query->where([
                'type' => $type
            ]);
        }
        return $query->paginate();
    }

    public function addOrEdit(Note $note) {
        if ($note && $note->patient_id && $note->author_id && $note->body && $note->type) {
            $savedNote = $this->model()->firstOrCreate([
                'patient_id' => $note->patient_id,
                'author_id' => $note->author_id,
                'type' => $note->type
            ]);
            $savedNote->update([
                'body' => $note->body,
                'isTCM' => $note->isTCM,
                'logger_id' => $note->logger_id,
                'did_medication_recon' => $note->did_medication_recon,
                'performed_at' => $note->performed_at
            ]);
            $savedNote->save();
            return $savedNote;
        }
        return $note;
    }

    public function add(Note $note) {
        if ($note && $note->patient_id && $note->author_id && $note->body && $note->type) {
            $note->performed_at = Carbon::now();
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