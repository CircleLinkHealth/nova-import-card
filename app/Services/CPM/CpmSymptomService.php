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

class CpmSymptomService implements CpmModel
{

    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        return $user->cpmSymptoms()->sync($ids);
    }
}
