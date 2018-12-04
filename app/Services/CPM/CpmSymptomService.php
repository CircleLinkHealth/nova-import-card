<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:21 PM
 */

namespace App\Services\CPM;

use App\Contracts\Services\CpmModel;
use App\User;
use App\Repositories\CpmSymptomRepository;

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
