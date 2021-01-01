<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Appointment;
use CircleLinkHealth\SharedModels\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Validator;

class AppointmentController extends Controller
{
    /**
     * @var AppointmentService
     */
    protected $appointmentService;

    /**
     * AppointmentController constructor.
     */
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function destroy($userId, $id)
    {
        return response()->json($this->appointmentService->removePatientAppointment($userId, $id));
    }

    public function show($userId)
    {
        return response()->json($this->appointmentService->repo()->patientAppointments($userId));
    }

    public function store($userId, Request $request)
    {
        $appointment              = new Appointment();
        $appointment->comment     = $request->input('comment');
        $appointment->patient_id  = $userId;
        $appointment->author_id   = auth()->user()->id;
        $appointment->type        = $request->input('type');
        $appointment->provider_id = $request->input('provider_id');
        $appointment->time        = $request->input('time');

        $date      = $request->input('date');
        $validator = Validator::make(['date' => $date], ['date' => 'date_format:m-d-Y|required']);

        if ($validator->fails()) {
            return \response("Date `$date` is invalid.", 400);
        }

        $carbonDate        = Carbon::createFromFormat('m-d-Y', $date);
        $appointment->date = $carbonDate->toDateString();

        if ($userId && $appointment->author_id && $appointment->type && $appointment->comment) {
            $appointment->save();

            return response()->json($appointment);
        }

        return \response('"user_id", "author_id", "type", "comment" and "provider_id" are required');
    }
}
