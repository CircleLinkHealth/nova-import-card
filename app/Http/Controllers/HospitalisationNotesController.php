<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HospitalisationNotesController extends Controller
{
    public function index() {
        return view('livewire.hospitalisation-notes');
    }
}
