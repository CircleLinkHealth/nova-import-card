<?php
namespace App\Repositories;


use App\Note;

class NoteRepository
{
    public function model()
    {
        return app(Note::class);
    }
}