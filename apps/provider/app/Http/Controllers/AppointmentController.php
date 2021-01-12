<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Appointment;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Validator;

class AppointmentController extends Controller
{
    public function create(Request $request, $patientId)
    {
        $patient   = User::find($patientId);
        $providers = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })
            ->orderBy('display_name')
            ->get(['id', 'display_name'])
            ->mapWithKeys(function ($provider) {
                return [$provider->id => $provider->display_name];
            })
            ->toArray();

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

        $validator = Validator::make(['date' => $input['date']], ['date' => 'date_format:m-d-Y|required']);

        if ($validator->fails()) {
            return 'Invalid date.';
        }

        $carbonDate = Carbon::createFromFormat('m-d-Y', $input['date']);

        $data = Appointment::create([
            'patient_id'    => $input['patientId'],
            'author_id'     => auth()->user()->id,
            'type'          => $input['appointment_type'],
            'provider_id'   => $providerId,
            'date'          => $carbonDate->toDateString(),
            'time'          => $input['time'],
            'comment'       => $input['comment'],
            'was_completed' => $was_completed,
        ]);

        return redirect()->route('patient.note.index', ['patientId' => $input['patientId']])->with(
            'messages',
            ['Successfully Stored Appointment']
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
            'userTimeZone' => $patient->timezone,
            'patient'      => $patient,
            'program_id'   => $patient->program_id,
        ];

        return view('wpUsers.patient.appointment.view', $view_data);
    }
}
