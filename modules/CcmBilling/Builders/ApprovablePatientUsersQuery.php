<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

trait ApprovablePatientUsersQuery
{
    public function approvablePatientUserQuery(int $patientId, Carbon $monthYear = null, bool $includeNotesControllerRalationships = false): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear, $includeNotesControllerRalationships)
            ->where('id', $patientId);
    }

    public function approvablePatientUsersQuery(Carbon $monthYear = null, bool $includeNotesControllerRalationships = false): Builder
    {
        $relations = [
            'primaryPractice' => function ($p) use ($includeNotesControllerRalationships) {
                $p->when($includeNotesControllerRalationships, fn ($pr) => $pr->with(['settings']))
                    ->with(['chargeableServices', 'pcmProblems', 'rpmProblems']);
            },
            'endOfMonthCcmStatusLogs' => function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            //todo: remove eager loads after refactor
            'attestedProblems' => function ($q) use ($monthYear) {
                $q->with('ccdProblem.cpmProblem')
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'billingProvider.user',
            'patientInfo.location.chargeableServiceSummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'ccdProblems' => function ($problem) {
                $problem->forBilling();
            },
            'chargeableMonthlySummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'chargeableMonthlyTime' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'forcedChargeableServices' => function ($f) use ($monthYear) {
                $f->with(['chargeableService'])
                    ->where(fn ($q)   => $q->when( ! is_null($monthYear), fn ($q) => $q->where('chargeable_month', $monthYear)))
                    ->orWhere(fn ($q) => $q->where('chargeable_month', null));
            },
            'inboundSuccessfulCalls' => function ($q) use ($monthYear){
                $q->createdInMonth($monthYear, 'called_date');
            },
            'monthlyBillingStatus' => function ($status) use ($monthYear){
                $status->createdOnIfNotNull($monthYear, 'chargeable_month');
            } 
        ];

        $notesControllerRelationships = [
            'carePlan' => function ($q) {
                return $q->select(['id', 'user_id', 'status', 'created_at']);
            },
            'patientSummaries' => function ($q) {
                return $q->where('month_year', Carbon::now()->startOfMonth());
            },
            'patientInfo' => function ($q) {
                return $q->with(['contactWindows']);
            },
            'careTeamMembers' => function ($q) {
                return $q->with([
                    'user' => function ($q) {
                        return $q->select(['id', 'first_name', 'last_name', 'suffix']);
                    },
                ])->has('user');
            }, ];

        return User::with($relations)
            ->when($includeNotesControllerRalationships, fn ($user) => $user->with($notesControllerRelationships))
            ->ofType('participant');
    }
}
