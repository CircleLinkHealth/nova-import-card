<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentCenterController extends Controller
{

    public function dashboard(){

        return view('enrollment-ui.dashboard');

    }

    public function training(){

        return view('enrollment-ui.training');

    }

}
