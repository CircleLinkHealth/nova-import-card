<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class HospitalisationNotesController extends Controller
{
    public function index()
    {
        return view('livewire.hospitalisation-notes');
    }
}
