<?php namespace App\Services;

use App\Repositories\AppointmentRepository;

class AppointmentService
{
    private $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo) {
        $this->appointmentRepo = $appointmentRepo;
    }

    public function repo() {
        return $this->appointmentRepo;
    }

    public function appointments() {
        return $this->repo()->appointments();
    }
}
