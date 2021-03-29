<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient as PatientModel;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PatientServiceProcessorRepository implements Repository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function attachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE, ?string $reason = null): void
    {
        //todo: revisit historical records accuracy from ABP edits
//        if (is_null($month)) {
//            PatientForcedChargeableService::create([
//                'patient_user_id'       => $patientId,
//                'chargeable_service_id' => $chargeableServiceId,
//                'action_type'           => $actionType,
//                'reason'                => $reason,
//            ]);
//
//            return;
//        }
        PatientForcedChargeableService::updateOrCreate(
            [
                'patient_user_id'       => $patientId,
                'chargeable_service_id' => $chargeableServiceId,
                'chargeable_month'      => $month,
            ],
            [
                'action_type' => $actionType,
                'reason'      => $reason,
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function createActivityForChargeableService(string $source, PageTimer $pageTimer, ChargeableServiceDuration $chargeableServiceDuration): Activity
    {
        return Activity::create(
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
    }

    public function detachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE): void
    {
        //We're retrieving because we need model event to be triggered for historical records
        optional(PatientForcedChargeableService::where('patient_user_id', $patientId)
            ->where('chargeable_service_id', $chargeableServiceId)
            ->where('chargeable_month', $month)
            ->where('action_type', $actionType)
            ->first())
            ->delete();
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

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): EloquentCollection
    {
        return new EloquentCollection(ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->with('chargeableService')
            ->where('chargeable_month', $month)
            ->get());
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableService())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->first();
    }

    public function getChargeablePatientTimesView(int $patientId, Carbon $month): EloquentCollection
    {
        $query = Activity::selectRaw("patient_id, patient_id AS patient_user_id, chargeable_service_id, cast(date_format(performed_at,'%Y-%m-01') as date) AS chargeable_month, sum(duration) AS total_time")
            ->where('patient_id', $patientId)
            ->createdInMonthFromDateTimeField($month, 'performed_at')
            ->groupBy(['patient_user_id', 'chargeable_service_id', 'chargeable_month']);
        return new EloquentCollection($query->get());
    }

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month = null): ?User
    {
        return $this
            ->approvablePatientUserQuery($patientId, $month)
            ->first();
    }

    public function getPatientWithBillingDataForNotesController(int $patientId): ?User
    {
        return $this->approvablePatientUserQuery($patientId, Carbon::now()->startOfMonth(), true)
            ->first();
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableService())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->exists();
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeableLocationMonthlySummary::where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableService())->getTable())
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
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableService())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->where('is_fulfilled', true)
            ->exists();
    }

    public function multiAttachServiceSummaries(Collection $processingOutputCollection): void
    {
        \DB::transaction(function () use ($processingOutputCollection) {
            foreach ($processingOutputCollection as $output) {
                ChargeablePatientMonthlySummary::updateOrCreate([
                    'patient_user_id'       => $output->getPatientUserId(),
                    'chargeable_service_id' => $output->getChargeableServiceId(),
                    'chargeable_month'      => $output->getChargeableMonth(),
                ], [
                    'is_fulfilled'             => $output->isFulfilling(),
                    'requires_patient_consent' => $output->requiresConsent(),
                ]);
            }
        }, 1);
    }

    public function requiresPatientConsent(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableService())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->where('requires_patient_consent', true)
            ->exists();
    }

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return $this->store($patientId, $chargeableServiceCode, $month, false);
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
