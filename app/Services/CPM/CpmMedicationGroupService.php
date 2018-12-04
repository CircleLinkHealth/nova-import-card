<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:20 PM
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\User;
use App\Repositories\CpmMedicationGroupRepository;

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
