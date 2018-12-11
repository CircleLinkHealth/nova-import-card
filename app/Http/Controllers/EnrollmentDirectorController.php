<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentDirectorController extends Controller
{
    public function index(){

        return view('admin.ca-director.index');

    }
}
