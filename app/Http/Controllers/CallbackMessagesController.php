<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CallbackMessagesController extends Controller
{
    public function index()
    {
        return view('livewire.callback-messages');
    }
}
