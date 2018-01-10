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

    /**
     * Scope for paused patients
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function paused()
    {
        $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->ccmStatus('paused');
            });

        return $this;
    }

    /**
     * Scope for patients whose paused letter was not printed yet
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function pausedLetterNotPrinted()
    {
        $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->whereNull('paused_letter_printed_at');
            });

        return $this;
    }

    public function fetch() {
        return $this->user->get();
    }
}