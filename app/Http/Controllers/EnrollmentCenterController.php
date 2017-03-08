<?php

namespace App\Http\Controllers;

use App\Enrollee;
use Illuminate\Http\Request;

class EnrollmentCenterController extends Controller
{

    public function dashboard(){

        //get an eligible patient.
        $enrollee = Enrollee::toCall()->first();

        return view('enrollment-ui.dashboard',
            [
                'enrollee' => $enrollee
            ]
        );

    }

    public function store(Request $request){

        dd($request->all());

    }

    public function training(){

        return view('enrollment-ui.training');

    }

}
