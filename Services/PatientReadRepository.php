<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Services;

use CircleLinkHealth\Customer\Filters\PatientFilters;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

class PatientReadRepository
{
    public function fetch()
    {
        return User::ofType('participant')
            ->with('patientInfo')->get();
    }

    public function patients(PatientFilters $filters)
    {
        $shouldSetDefaultRows = false;
        $filtersInput         = $filters->filters();

        $showPracticePatientsInput = $filtersInput['showPracticePatients'] ?? null;
        $isProvider                = auth()->user()->isProvider();
        $showPracticePatients      = true;
        if ($isProvider && (User::SCOPE_LOCATION === auth()->user()->scope || 'false' === $showPracticePatientsInput)) {
            $showPracticePatients = false;
        }

        $users = User::ofType('participant')
            ->with([
                'carePlan' => function ($q) {
                    $q->select(['user_id', 'status']);
                },
                'phoneNumbers' => function ($q) {
                    $q->select(['user_id', 'is_primary', 'number', 'type']);
                },
                'patientSummaries' => function ($q) {
                    $q->whereMonthYear(Carbon::now()->startOfMonth()->toDateString())
                        ->select(['patient_id', 'ccm_time', 'bhi_time', 'month_year']);
                },
                'patientInfo.location',
                'careTeamMembers' => function ($q) {
                    $q->whereIn(
                        'type',
                        [CarePerson::BILLING_PROVIDER, CarePerson::REGULAR_DOCTOR]
                    )->with(['user' => function ($q) {
                        $q->without(['perms', 'roles'])
                            ->select(['id', 'first_name', 'last_name', 'suffix', 'display_name']);
                    }]);
                },
                'observations' => function ($q) {
                    $q->latest();
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
                false === $showPracticePatients,
                function ($query) {
                    $query->whereHas('careTeamMembers', function ($subQuery) {
                        $subQuery->where('member_user_id', auth()->id())
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
        User::ofType('participant')
            ->with('patientInfo')
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
        User::ofType('participant')
            ->with('patientInfo')
            ->whereHas('patientInfo', function ($q) {
                $q->whereNull('paused_letter_printed_at');
            });

        return $this;
    }

    /**
     * Scope for unreachable patients().
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function unreachable()
    {
        User::ofType('participant')
            ->with('patientInfo')
            ->whereHas('patientInfo', function ($q) {
                $q->ccmStatus(Patient::UNREACHABLE);
            });

        return $this;
    }
}
