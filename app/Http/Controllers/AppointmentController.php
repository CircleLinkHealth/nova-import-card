<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Appointment;
use App\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create(Request $request, $patientId)
    {
        $patient = User::find($patientId);

        $providers    = [];
        $providerList = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->get();

        foreach ($providerList as $provider) {
            if ($provider->getFullName()) {
                $providers[$provider->id] = $provider->getFullName();
            }
        }

        asort($providers);

        $data = [
            'providers' => $providers,
            'patientId' => $patient->id,
            'patient'   => $patient,
        ];

        return view('wpUsers.patient.appointment.create', $data);
    }

    public function index()
    {
    }

    public function store(Request $request)
    {
        $input = $request->input();

        $was_completed = isset($input['is_completed']) ?? false;

        $providerId = 'null' != $input['provider'] ? $input['provider'] : null;

        $data = Appointment::create([
            'patient_id'    => $input['patientId'],
            'author_id'     => auth()->user()->id,
            'type'          => $input['appointment_type'],
            'provider_id'   => $providerId,
            'date'          => $input['date'],
            'time'          => $input['time'],
            'comment'       => $input['comment'],
            'was_completed' => $was_completed,
        ]);

        return redirect()->route('patient.note.index', ['patient' => $input['patientId']])->with(
            'messages',
            ['Successfully Created Note']
        );
    }

    public function view(Request $request, $patientId, $appointmentId)
    {
        $patient     = User::find($patientId);
        $appointment = Appointment::find($appointmentId);

        //Set up note packet for view
        $data = [];

        $data['type'] = $appointment->type;
        $data['id']   = $appointment->id;
        $data['date'] = $appointment->date;
        $data['time'] = $appointment->time;
        $provider     = User::find($appointment->provider_id);
        if ($provider) {
            $data['provider_name'] = $provider->getFullName();

            if ($provider->getSpecialty()) {
                $data['provider_name'] = "{$data['provider_name']}, {$provider->getSpecialty()}";
            }
        } else {
            $data['provider_name'] = '';
        }

        $data['comment']      = $appointment->comment;
        $data['type']         = $appointment->type;
        $data['is_completed'] = $appointment->was_completed;

        $view_data = [
            'appointment'  => $data,
            'userTimeZone' => $patient->timeZone,
            'patient'      => $patient,
            'program_id'   => $patient->program_id,
        ];

        return view('wpUsers.patient.appointment.view', $view_data);
    }
}
