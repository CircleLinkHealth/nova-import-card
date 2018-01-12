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

    public function count() {
        return $this->model()->count();
    }

    public function exists($id) {
        return !!$this->model()->find($id);
    }

    public function appointments() {
        return $this->model()->paginate();
    }
}