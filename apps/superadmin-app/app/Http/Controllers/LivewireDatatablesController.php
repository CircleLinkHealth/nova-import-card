<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LivewireDatatablesController extends Controller
{
    public function callAttemptNote() {
        return view('livewire.call-attempt-note');
    }
    
    public function hospitalisationNotes()
    {
        return view('livewire.hospitalisation-notes');
    }
    
    public function messageDispatchMessages()
    {
        return view('livewire.message-dispatch-messages');
    }
}
