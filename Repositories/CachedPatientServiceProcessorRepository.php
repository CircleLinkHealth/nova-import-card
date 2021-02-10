<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as RepositoryInterface;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CachedPatientServiceProcessorRepository implements RepositoryInterface
{
    protected PatientServiceProcessorRepository $repo;

    public function __construct()
    {
        $this->repo = new PatientServiceProcessorRepository();
    }

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
        $summary = $this->repo->fulfill($patientId, $chargeableServiceCode, $month);

        $patient = $this->getPatientFromCache($patientId);

        $patient
            ->chargeableMonthlySummaries
            ->firstWhere('id', $summary->id)
            ->is_fulfilled = true;

        if ($patient->relationLoaded('chargeableMonthlySummariesView')) {
            $patient
                ->chargeableMonthlySummariesView
                ->firstWhere('id', $summary->id)
                ->is_fulfilled = true;
        }

        return $summary;
    }

    /**
     * @throws \Exception
     */
    public function getChargeablePatientSummaries(int $patientId, Carbon $month): EloquentCollection
    {
        $patient = $this->getPatientFromCache($patientId);

        if (is_null($patient)) {
            return new EloquentCollection();
        }

        return $patient->chargeableMonthlySummariesView
            ->where('chargeable_month', $month);
    }

    /**
     * @throws \Exception
     */
    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        //todo: query for view if you should
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummariesView
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->first();
    }

    /**
     * @throws \Exception
     */
    public function getPatientWithBillingDataForMonth(int $patientId, ?Carbon $month = null): ?User
    {
        //is it realistic that multiple months are going to be queried by this?
        //really have to account for other months as well. Maybe don't load only for specific month from the original repo and load everything?
        //also if we load all, we have to specify summary access for other cases
        return $this->getPatientFromCache($patientId, $month);
    }

    /**
     * @throws \Exception
     */
    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId, $month)
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    /**
     * @throws \Exception
     */
    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->patientInfo
            ->location
            ->chargeableServiceSummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    /**
     * @throws \Exception
     */
    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->where('is_fulfilled', true)
            ->count() > 0;
    }

    /**
     * @throws \Exception
     */
    public function reloadPatientProblems(int $patientId): void
    {
        if (BillingCache::patientExistsInCache($patientId)) {
            $this->getPatientFromCache($patientId)
                ->load(['ccdProblems' => function ($problem) {
                    $problem->forBilling();
                }]);
        }
    }

    /**
     * @throws \Exception
     */
    public function reloadPatientSummaryViews(int $patientId, Carbon $month): void
    {
        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG) && BillingCache::patientExistsInCache($patientId)) {
            $this->getPatientFromCache($patientId)
                ->load(['chargeableMonthlySummariesView' => function ($q) use ($month) {
                    $q->createdOnIfNotNull($month, 'chargeable_month');
                }]);
        }
    }

    /**
     * @throws \Exception
     */
    public function requiresPatientConsent(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->where('requires_patient_consent', true)
            ->count() > 0;
    }

    /**
     * @throws \Exception
     */
    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->setPatientConsented($patientId, $chargeableServiceCode, $month);

        $patient = $this->getPatientFromCache($patientId);

        $patient->chargeableMonthlySummaries
            ->firstWhere('id', $summary->id)
            ->requires_patient_consent = false;

        if ($patient->relationLoaded('chargeableMonthlySummariesView')) {
            $patient->chargeableMonthlySummariesView
                ->firstWhere('id', $summary->id)
                ->requires_patient_consent = false;
        }

        return $summary;
    }

    /**
     * @throws \Exception
     */
    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->store($patientId, $chargeableServiceCode, $month, $requiresPatientConsent);

        $patient = $this->getPatientFromCache($patientId);

        //todo: is the possibily of duplicates with different id real?
        if ($patient->chargeableMonthlySummaries->contains('id', $summary->id)) {
            $patient->chargeableMonthlySummaries->forgetUsingModelKey('id', $summary->id);
        }

        $patient->chargeableMonthlySummaries
            ->push($summary);

        if ($patient->relationLoaded('chargeableMonthlySummariesView')) {
            if ($patient->chargeableMonthlySummariesView->contains('id', $summary->id)) {
                $patient->chargeableMonthlySummariesView->forgetUsingModelKey('id', $summary->id);
            }

            $patient->chargeableMonthlySummariesView->push(
                ChargeablePatientMonthlySummaryView::firstWhere('id', $summary->id)
            );
        }

        return $summary;
    }

    /**
     * @throws \Exception
     */
    private function getPatientFromCache(int $patientId, ?Carbon $month = null): ?User
    {
        $this->retrievePatientDataIfYouMust($patientId, $month);

        $patient = BillingCache::getPatient($patientId);

        if (is_null($patient)) {
            sendSlackMessage('#billing_alerts', "Warning! (From cached repo:) Could not find Patient with id: $patientId in the Billing Cache.");

            return null;
        }

        if (is_null($patient->patientInfo)) {
            sendSlackMessage('#billing_alerts', "Warning! (From cached repo:) Patient with id: $patientId does not have patient info attached.");
        }

        if (is_null(optional($patient->patientInfo)->preferred_contact_location)) {
            sendSlackMessage('#billing_alerts', "Warning! (From cached repo:) Patient with id: $patientId does not have a preferred contact location.");
        }

        return $patient;
    }

    private function queryPatientData(int $patientId, ?Carbon $month = null)
    {
        $patient = $this->repo->getPatientWithBillingDataForMonth($patientId, $month);
        BillingCache::setQueriedPatient($patientId);

        if (is_null($patient)) {
            sendSlackMessage('#billing_alerts', "Warning! (From cached repo:) Patient: $patientId not found.");

            return;
        }
        BillingCache::setPatientInCache($patient);
    }

    private function retrievePatientDataIfYouMust(int $patientId, ?Carbon $month = null): void
    {
        if (BillingCache::patientWasQueried($patientId)) {
            return;
        }

        $this->queryPatientData($patientId, $month);
    }

    public function attachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE, ?string $reason =null):void
    {
        $this->repo->attachForcedChargeableService($patientId, $chargeableServiceId, $month, $actionType, $reason);
    }

    public function detachForcedChargeableService(int $patientId, int $chargeableServiceId, Carbon $month = null, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE):void
    {
        $this->repo->detachForcedChargeableService($patientId, $chargeableServiceId, $month, $actionType);
    }
}
