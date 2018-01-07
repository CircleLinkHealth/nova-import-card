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

    public function __construct(CpmLifestyleRepository $lifestyleRepo, CpmLifestyleUserRepository $lifestyleUserRepo) {
        $this->lifestyleRepo = $lifestyleRepo;
        $this->lifestyleUserRepo = $lifestyleUserRepo;
    }

    public function repo() {
        return $this->lifestyleRepo;
    }

    public function lifestyles() {
        return $this->repo()->lifestyles();
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmLifestyles()->sync($ids);
    }
}
