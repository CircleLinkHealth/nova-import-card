<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\PatientInfo;
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

        $input = $request->input();

        $was_completed = isset($input['is_completed']) ?? false;

        $data = Appointment::create([

            'patient_id' => $input['patientId'],
            'author_id' => auth()->user()->id,
            'provider_id' => $input['provider'],
            'date' => $input['date'],
            'time' => $input['time'],
            'comment' => $input['comment'],
            'was_completed' => $was_completed,

        ]);

        return redirect()->route('patient.note.index', ['patient' => $input['patientId']])->with('messages',
            ['Successfully Created Note']);

    }

    public function view(Request $request){


        $data = [

        ];

        return view('patient.appointment.view', $data);

    }
    
}
