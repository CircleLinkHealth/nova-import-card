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
use App\Repositories\CpmBiometricUserRepository;

class CpmBiometricService implements CpmModel
{
    private $biometricUserRepo;

    public function __construct(CpmBiometricUserRepository $biometricUserRepo) {
        $this->biometricUserRepo = $biometricUserRepo;
    }

    public function repo() {
        return $this->biometricUserRepo;
    }

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmBiometrics()->sync($ids);
    }
}
