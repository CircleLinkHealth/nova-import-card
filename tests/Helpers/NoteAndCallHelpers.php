<?php namespace Tests\Helpers;

use Carbon\Carbon;

trait NoteAndCallHelpers
{

    public function createNote(){

        $this->actingAs($this->provider)
            ->visit("/manage-patients/{$this->patient->id}/notes/create")
            //Select Note Topic
            ->select('General (Clinical)', 'type')
            //Fill in 'When'
            ->type(Carbon::now()->format('Y-m-d\TH:i'), 'performed_at')
            //Check to see if looged in user is selected in 'Performed By'
            ->see($this->provider->fullName)
            //Enter a note
            ->type('Just Recorded some vitals for patient.', 'body')
            //Add someone to email
            ->select('Patient Support', 'careteam[]')
            ->press('Submit')
            ->seePageIs("/manage-patients/{$this->patient->id}/notes");

    }

}
                                         