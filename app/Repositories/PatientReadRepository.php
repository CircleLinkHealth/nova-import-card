<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/10/2018
 * Time: 8:59 PM
 */

namespace App\Repositories;


use App\User;

class PatientReadRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user::ofType('participant')
                           ->with('patientInfo');
    }

    public function paused()
    {
        return $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', '=', 'paused');
            });
    }
}