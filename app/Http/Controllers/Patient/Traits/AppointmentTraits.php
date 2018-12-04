<?php

namespace App\Http\Controllers\Patient\Traits;

use App\Appointment;
use Illuminate\Http\Request;

trait AppointmentTraits
{
    public function addAppointment($userId, Request $request)
    {
        $appointment = new Appointment();
        $appointment->comment = $request->input('comment');
        $appointment->patient_id = $userId;
        $appointment->author_id = auth()->user()->id;
        $appointment->type = $request->input('type');
        $appointment->provider_id = $request->input('provider_id');
        $appointment->date = $request->input('date');
        $appointment->time = $request->input('time');
        if ($userId && $appointment->author_id && $appointment->type && $appointment->comment) {
            return response()->json($this->appointmentService->repo()->create($appointment));
        } else {
            return $this->badRequest('"user_id", "author_id", "type", "comment" and "provider_id" are required');
        }
    }

    public function getAppointments($userId)
    {
        return response()->json($this->appointmentService->repo()->patientAppointments($userId));
    }

    public function removeAppointment($userId, $id)
    {
        return response()->json($this->appointmentService->removePatientAppointment($userId, $id));
    }
}
