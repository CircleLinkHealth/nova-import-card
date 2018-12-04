<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Appointment;

class AppointmentRepository
{
    public function model()
    {
        return app(Appointment::class);
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function exists($id)
    {
        return !!$this->model()->find($id);
    }

    public function appointments()
    {
        return $this->model()->orderBy('id', 'desc')->paginate();
    }

    public function appointment($id)
    {
        return $this->model()->findOrFail($id);
    }

    public function patientAppointments($userId)
    {
        return $this->model()->where([ 'patient_id' => $userId ])->orderBy('id', 'desc')->paginate(5);
    }

    public function create(Appointment $appointment)
    {
        $appointment->save();
        return $appointment;
    }

    public function belongsToUser($id, $userId)
    {
        return !!$this->model()->where([ 'id' => $id, 'patient_id' => $userId ])->first();
    }

    public function remove($id)
    {
        $this->model()->where([ 'id' => $id ])->delete();
        return [
            'message' => 'successful'
        ];
    }
}
