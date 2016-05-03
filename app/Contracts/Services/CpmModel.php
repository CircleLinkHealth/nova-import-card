<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:24 PM
 */
namespace App\Contracts\Services;

use App\CarePlanTemplate;
use App\User;

interface CpmModel
{
    public function syncWithUser(User $user, array $ids, $page = null);
}