<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function index(){

        

    }
    
    public function create(){

        $data = [

        ];


        return view('patient.appointment.create', $data);
        
    }

    public function store(Request $request){

        $data = [

        ];

        return view('', $data);

    }

    public function view(Request $request){


        $data = [

        ];

        return view('patient.appointment.view', $data);

    }
    
}
