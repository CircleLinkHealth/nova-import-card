<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\Repositories\CpmLifestyleRepository;
use App\Repositories\CpmLifestyleUserRepository;
use App\User;

class CpmLifestyleService implements CpmModel
{
    private $lifestyleRepo;
    private $lifestyleUserRepo;

    public function __construct(CpmLifestyleRepository $lifestyleRepo, CpmLifestyleUserRepository $lifestyleUserRepo)
    {
        $this->lifestyleRepo = $lifestyleRepo;
        $this->lifestyleUserRepo = $lifestyleUserRepo;
    }

    public function repo()
    {
        return $this->lifestyleRepo;
    }

    public function lifestylePatients($lifestyleId)
    {
        return $this->lifestyleUserRepo->lifestylePatients($lifestyleId);
    }

    public function patientLifestyles($userId)
    {
        return $this->lifestyleUserRepo->patientLifestyles($userId);
    }

    public function addLifestyleToPatient($lifestyleId, $userId)
    {
        if ($this->repo()->exists($lifestyleId)) {
            return $this->lifestyleUserRepo->addLifestyleToPatient($lifestyleId, $userId);
        } else {
            throw new Exception('lifestyle with id "' . $lifestyleId . '" does not exist');
        }
    }

    public function removeLifestyleFromPatient($lifestyleId, $userId)
    {
        return $this->lifestyleUserRepo->removeLifestyleFromPatient($lifestyleId, $userId);
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmLifestyles()->sync($ids);
    }
}
