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

    public function model()
    {
        return $this->user;
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

    public function fetch($resetQuery = true)
    {
        $result = $this->user->get();

        if ($resetQuery) {
            $this->user = User::ofType('participant')
                              ->with('patientInfo');
        }

        return $result;
    }
}