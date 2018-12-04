<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Filters\PatientFilters;
use App\Patient;
use App\PatientSearchModel;
use App\User;

class PatientReadRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user::ofType('participant')
            ->with('patientInfo');
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

    public function model()
    {
        return $this->user;
    }

    public function patients(PatientFilters $filters)
    {
        $users = $this->model()
            ->with([
                'carePlan',
                'phoneNumbers',
                'patientInfo',
                'primaryPractice',
                'providerInfo',
                'observations' => function ($q) {
                    $q
                        ->latest();
                },
            ])
            ->whereHas('patientInfo')
            ->intersectPracticesWith(auth()->user())
            ->filter($filters);
        if (!$filters->isExcel()) { //check that an excel file is not requested]
            if (isset($filters->filters()['rows']) && 'all' == $filters->filters()['rows']) {
                $users = $users->paginate($users->count());
            } else {
                $users = $users->paginate($filters->filters()['rows'] ?? 15);
            }
        } else {
            if (isset($filters->filters()['rows']) && is_integer((int) $filters->filters()['rows'])) {
                $users = $users->paginate($filters->filters()['rows']);
            } else {
                $users = $users->paginate($users->count());
            }
        }

        return $users;
    }

    /**
     * Scope for paused patients.
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
     * Scope for patients whose paused letter was not printed yet.
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

    public function search(PatientSearchModel $searchModel)
    {
        return $searchModel->results();
    }

    /**
     * Scope for unreachable patients().
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function unreachable()
    {
        $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->ccmStatus(Patient::UNREACHABLE);
            });

        return $this;
    }
}
