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

class CpmBiometricService implements CpmModel
{

    public function syncWithUser(User $user, array $ids = [], $page = null)
    {
        return $user->cpmBiometrics()->sync($ids);
    }
}