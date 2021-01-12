<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

use CircleLinkHealth\SharedModels\Repositories\AppointmentRepository;

class AppointmentService
{
    private $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    public function appointments()
    {
        return $this->repo()->appointments();
    }

    public function removePatientAppointment($userId, $id)
    {
        if ($this->repo()->belongsToUser($id, $userId)) {
            return $this->repo()->remove($id);
        }
        throw new \Exception('user with id "'.$userId.'" does not own appointment with id "'.$id.'"');
    }

    public function repo()
    {
        return $this->appointmentRepo;
    }
}
