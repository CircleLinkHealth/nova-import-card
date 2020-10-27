<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use App\Jobs\ChargeableServiceDuration;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService as ChargeableServiceModel;
use CircleLinkHealth\Customer\Entities\Patient as PatientModel;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Database\Eloquent\Collection;

class PatientServiceProcessorRepository implements Repository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    /**
     * @throws \Exception
     */
    public function createActivityForChargeableService(string $source, PageTimer $pageTimer, ChargeableServiceDuration $chargeableServiceDuration): Activity
    {
        $activity = Activity::create(
            [
                'type'                  => $pageTimer->activity_type,
                'provider_id'           => $pageTimer->provider_id,
                'is_behavioral'         => $chargeableServiceDuration->isBehavioral,
                'performed_at'          => $pageTimer->start_time,
                'duration'              => $chargeableServiceDuration->duration,
                'duration_unit'         => 'seconds',
                'patient_id'            => $pageTimer->patient_id,
                'logged_from'           => $source,
                'logger_id'             => $pageTimer->provider_id,
                'page_timer_id'         => $pageTimer->id,
                'chargeable_service_id' => $chargeableServiceDuration->id,
            ]
        );

        $this->reloadPatientSummaryViews($pageTimer->patient_id, Carbon::parse($pageTimer->start_time)->startOfMonth());

        return $activity;
    }

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
        ], [
            'is_fulfilled' => true,
        ]);
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): Collection
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->get();
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->first();
    }

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month = null): ?User
    {
        return $this
            ->approvablePatientUserQuery($patientId, $month)
            //todo: check breaking points or if this should be here instead of elsewhere
//            ->with(['patientInfo.location.chargeableServiceSummaries' => function ($summary) use ($month) {
//                $summary->with(['chargeableService'])
//                    ->createdOn($month, 'chargeable_month');
//            }])
            ->first();
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->exists();
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeableLocationMonthlySummary::where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableServiceModel())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->whereIn('location_id', function ($q) use ($patientId) {
                $q->select('preferred_contact_location')
                    ->from((new PatientModel())->getTable())
                    ->where('user_id', $patientId);
            })->exists();
    }

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('is_fulfilled', true)
            ->exists();
    }

    public function reloadPatientProblems(int $patientId): void
    {
        // TODO: Implement reloadPatientProblems() method.
    }

    public function reloadPatientSummaryViews(int $patientId, Carbon $month): void
    {
        // TODO: Implement reloadPatientSummaryViews() method.
    }

    public function requiresPatientConsent(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('requires_patient_consent', true)
            ->exists();
    }

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
        ], [
            'requires_patient_consent' => false,
        ]);
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate(
            [
                'patient_user_id'       => $patientId,
                'chargeable_month'      => $month,
                'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
            ],
            [
                'requires_patient_consent' => $requiresPatientConsent,
            ]
        );
    }
}
