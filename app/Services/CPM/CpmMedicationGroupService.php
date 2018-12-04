<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Repositories\CpmMedicationGroupRepository;
use App\User;

class CpmMedicationGroupService implements CpmModel
{
    private $medicationGroupRepo;

    public function __construct(CpmMedicationGroupRepository $medicationGroupRepo)
    {
        $this->medicationGroupRepo = $medicationGroupRepo;
    }

    public function repo()
    {
        return $this->medicationGroupRepo;
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmMedicationGroups()->sync($ids);
    }
}
