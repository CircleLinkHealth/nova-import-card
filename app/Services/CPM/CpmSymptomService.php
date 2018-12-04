<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Repositories\CpmSymptomRepository;
use App\User;

class CpmSymptomService implements CpmModel
{
    private $symptomRepo;

    public function __construct(CpmSymptomRepository $symptomRepo)
    {
        $this->symptomRepo = $symptomRepo;
    }

    public function repo()
    {
        return $this->symptomRepo;
    }

    public function symptoms()
    {
        return $this->repo()->symptoms();
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmSymptoms()->sync($ids);
    }
}
