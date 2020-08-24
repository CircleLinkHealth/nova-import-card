<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Filters\PatientFilters;
use App\PatientSearchModel;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

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
        $shouldSetDefaultRows = false;
        $filtersInput         = $filters->filters();

        $showPracticePatients = $filtersInput['showPracticePatients'] ?? null;

        $users = $this->model()
            ->with([
                'carePlan',
                'phoneNumbers',
                'patientInfo.location',
                'primaryPractice',
                'providerInfo',
                'billingProvider',
                'observations' => function ($q) {
                    $q
                        ->latest();
                },
            ])
            ->when(array_key_exists('patientsPendingAuthUserApproval', $filtersInput), function ($q) {
                if (auth()->user()->canApproveCarePlans()) {
                    $q->patientsPendingProviderApproval(auth()->user());
                } elseif (auth()->user()->isAdmin()) {
                    $q->patientsPendingCLHApproval(auth()->user());
                }
            })
            ->when(
                auth()->user()->isProvider() && 'false' == $showPracticePatients,
                function ($query) {
                    $query->whereHas('careTeamMembers', function ($subQuery) {
                        $subQuery->where('member_user_id', auth()->user()->id)
                            ->whereIn(
                                'type',
                                [CarePerson::BILLING_PROVIDER, CarePerson::REGULAR_DOCTOR]
                            );
                    });
                }
            )
            ->whereHas('patientInfo')
            ->intersectPracticesWith(auth()->user())
            ->filter($filters);

        if ( ! isset($filtersInput['rows'])) {
            $shouldSetDefaultRows = true;
        } elseif ('all' !== $filtersInput['rows'] && ! is_numeric($filtersInput['rows'])) {
            $shouldSetDefaultRows = true;
        }

        if ($shouldSetDefaultRows) {
            $filtersInput['rows'] = 15;
        }

        if ('all' == $filtersInput['rows']) {
            $users = $users->paginate($users->count());
        } else {
            $users = $users->paginate($filtersInput['rows']);
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
