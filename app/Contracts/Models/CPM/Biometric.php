<?php
/**
 * Created by PhpStorm.
 * User: rohan
 * Date: 5/9/16
 * Time: 3:42 PM
 */

namespace App\Contracts\Models\CPM;

use App\User;

interface Biometric
{
    public function getUserValues(User $user);
}
