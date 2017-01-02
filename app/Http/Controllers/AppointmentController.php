<?php

namespace App\Http\Controllers;

use App\Appointment;
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
            'type' =>  $input['appointment_type'],
            'provider_id' => $input['provider'],
            'date' => $input['date'],
            'time' => $input['time'],
            'comment' => $input['comment'],
            'was_completed' => $was_completed,

        ]);

        return redirect()->route('patient.note.index', ['patient' => $input['patientId']])->with('messages',
            ['Successfully Created Note']);

    }

    public function view(Request $request, $patientId, $appointmentId){

        $patient = User::find($patientId);
        $appointment = Appointment::find($appointmentId);

        //Set up note packet for view
        $data = [];


        $data['type'] = $appointment->type;
        $data['id'] = $appointment->id;
        $data['date'] = $appointment->date;
        $data['time'] = $appointment->time;
        $provider = User::find($appointment->provider_id);
        if ($provider) {
            $data['provider_name'] = $provider->fullName;
        } else {
            $data['provider_name'] = '';
        }

        $data['comment'] = $appointment->comment;
        $data['type'] = $appointment->type;
        $data['is_completed'] = $appointment->was_completed;
;

        $view_data = [
            'appointment'          => $data,
            'userTimeZone'  => $patient->timeZone,
            'patient'       => $patient,
            'program_id'    => $patient->program_id,
        ];

        return view('wpUsers.patient.appointment.view', $view_data);

    }
    
}
