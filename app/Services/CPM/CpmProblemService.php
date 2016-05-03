<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CPM;


use App\Contracts\Services\CpmModel;
use App\User;

class CpmProblemService implements CpmModel
{

    public function syncWithUser(User $user, array $ids = [], $page = null)
    {
        return $user->cpmProblems()->sync($ids);
    }
}