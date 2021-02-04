<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\CPM;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Contracts\CpmModel;
use CircleLinkHealth\SharedModels\Repositories\CpmMedicationGroupRepository;

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
