<?php namespace App\Services;

use App\Repositories\AppointmentRepository;

class AppointmentService
{
    private $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    public function repo()
    {
        return $this->appointmentRepo;
    }

    public function appointments()
    {
        return $this->repo()->appointments();
    }

    public function removePatientAppointment($userId, $id)
    {
        if ($this->repo()->belongsToUser($id, $userId)) {
            return $this->repo()->remove($id);
        } else {
            throw new \Exception('user with id "' . $userId . '" does not own appointment with id "' . $id . '"');
        }
    }
}
