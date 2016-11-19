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

        $providers = [];
        $providerList = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->get()->all();

        foreach ($providerList as $provider){
                if ($provider->fullName) {
                    $providers[$provider->id] = $provider->fullName;
                }
        }
        
        asort($providers);


        $data = [

            'providers' => $providers,
            'patientId' => $patient->id,
            'patient' => $patient

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
