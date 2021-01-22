<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\CPM;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Contracts\CpmModel;
use CircleLinkHealth\SharedModels\Repositories\CpmLifestyleRepository;
use CircleLinkHealth\SharedModels\Repositories\CpmLifestyleUserRepository;

class CpmLifestyleService implements CpmModel
{
    private $lifestyleRepo;
    private $lifestyleUserRepo;

    public function __construct(CpmLifestyleRepository $lifestyleRepo, CpmLifestyleUserRepository $lifestyleUserRepo)
    {
        $this->lifestyleRepo     = $lifestyleRepo;
        $this->lifestyleUserRepo = $lifestyleUserRepo;
    }

    public function addLifestyleToPatient($lifestyleId, $userId)
    {
        if ($this->repo()->exists($lifestyleId)) {
            return $this->lifestyleUserRepo->addLifestyleToPatient($lifestyleId, $userId);
        }
        throw new \Exception('lifestyle with id "'.$lifestyleId.'" does not exist');
    }

    public function lifestylePatients($lifestyleId)
    {
        return $this->lifestyleUserRepo->lifestylePatients($lifestyleId);
    }

    public function removeLifestyleFromPatient($lifestyleId, $userId)
    {
        return $this->lifestyleUserRepo->removeLifestyleFromPatient($lifestyleId, $userId);
    }

    public function repo()
    {
        return $this->lifestyleRepo;
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmLifestyles()->sync($ids);
    }
}
