<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\Customer\Entities\Appointment;

class AppointmentRepository
{
    public function appointment($id)
    {
        return $this->model()->findOrFail($id);
    }

    public function appointments()
    {
        return $this->model()->orderBy('id', 'desc')->paginate();
    }

    public function belongsToUser($id, $userId)
    {
        return (bool) $this->model()->where(['id' => $id, 'patient_id' => $userId])->first();
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function exists($id)
    {
        return (bool) $this->model()->find($id);
    }

    public function model()
    {
        return app(Appointment::class);
    }

    public function patientAppointments($userId)
    {
        return $this->model()->where(['patient_id' => $userId])->orderBy('id', 'desc')->paginate(5);
    }

    public function remove($id)
    {
        $this->model()->where(['id' => $id])->delete();

        return [
            'message' => 'successful',
        ];
    }
}
