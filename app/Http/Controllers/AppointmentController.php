<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function index(){

        

    }
    
    public function create(Request $request, $patientId){

        $patient = User::find($patientId);

        $data = [

            'patient' => $patient,

        ];


        return view('wpUsers.patient.appointment.create', $data);
        
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
