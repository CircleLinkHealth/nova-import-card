<?php
namespace App\Repositories;


use App\Note;

class NoteRepository
{
    public function model()
    {
        return app(Note::class);
    }
    
    public function count() {
        return $this->model()->count();
    }

    public function patientNotes($userId) {
        return $this->model()->where([
            'patient_id' => $userId
        ])->paginate();
    }
}