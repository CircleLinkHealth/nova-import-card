<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class MessageDispatchMessagesController extends Controller
{
    public function index()
    {
        return view('livewire.message-dispatch-messages');
    }
}
