<?php
namespace App\Repositories;


use App\Note;

class PatientRepository
{
    public function model()
    {
        return app(Note::class);
    }
}