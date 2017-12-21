<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CareController extends Controller
{
    public function enroll(Request $request)
    {
        return view('care.index');        
    }
}
