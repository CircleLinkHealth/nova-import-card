<?php

use App\Note;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NoteTest extends TestCase
{

    protected $note;
    protected $patient;
    protected $author;

    /** @test */
    public function test_notes_migrations()
    {

        $note = Note::random();

        dd($note);

    }

}
