<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Exception;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Collection;

class AutoPatientAttestation
{
    private ?Carbon $month  = null;
    private ?User $patient  = null;
    private ?int $patientId = null;
    private ?int $pmsId     = null;

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    private function setup()
    {
        if ( ! $this->month) {
            throw new Exception('need to supply month');
        }

        if ( ! $this->patient && ! $this->patientId) {
            throw new Exception('need to supply patient');
        }

        if ($this->patient) {
            return;
        }

        if ( ! $this->pmsId) {
            throw new Exception('need to supply pmsId when supplying only patientId');
        }

        $relations = [
            'attestedProblems' => function ($q) {
                $q->with('ccdProblem.cpmProblem')
                    ->createdOnIfNotNull($this->month, 'chargeable_month');
            },
        ];

        if ($this->isNewBillingEnabled()) {
            $relations['chargeableMonthlySummaries'] = function ($q) {
                $q->createdOnIfNotNull($this->month, 'chargeable_month');
            };
        } else {
            $relations['patientSummaries'] = function ($q) {
                $q->with('chargeableServices')
                    ->createdOnIfNotNull($this->month, 'month_year');
            };
        }

        $this->patient = User::with($relations)->find($this->patientId);
    }

    /**
     * @throws Exception
     */
    public function executeIfYouShould()
    {
        $this->setup();

        //Auto attest only for past months, to not mess with real attestations
        if ($this->month->gt(now()->subMonth()->endOfMonth())) {
            return;
        }

        if ($this->unAttestedPcm() || $this->unAttestedCcm()) {
            $this->attachProblems($this->getCcmProblemsForAutoAttestation());
        }

        if ($this->unAttestedBhi()) {
            $this->attachProblems($this->getBhiProblemsForAutoAttestation());
        }
    }

    public static function fromId(int $patientId): AutoPatientAttestation
    {
        $result            = new AutoPatientAttestation();
        $result->patientId = $patientId;

        return $result;
    }

    public static function fromUser(User $patient): AutoPatientAttestation
    {
        $result          = new AutoPatientAttestation();
        $result->patient = $patient;

        return $result;
    }

    /**
     * @throws Exception
     */
    public function getBhiAttestedProblems(): Collection
    {
        $this->setup();

        $hasBhi = $this->hasServiceCode(ChargeableService::BHI);
        if ( ! $hasBhi) {
            return collect();
        }

        return $this->patient->attestedProblems
            ->where('ccdProblem.cpmProblem.is_behavioral', '=', true);
    }

    /**
     * @throws Exception
     */
    public function getCcmAttestedProblems(): Collection
    {
        $this->setup();

        $hasRpm = $this->hasServiceCode(ChargeableService::RPM);
        if ($hasRpm) {
            return $this->patient->ccdProblems->map(function ($prob) {
                return [
                    'id'            => $prob->id,
                    'name'          => $prob->name,
                    'code'          => $prob->icd10Code(),
                    'is_behavioral' => $prob->isBehavioral(),
                ];
            })->unique('code')->filter()->values()->pluck('id');
        }

        $hasBhi           = $this->hasServiceCode(ChargeableService::BHI);
        $attestedProblems = $this->patient->attestedProblems;
        if ($hasBhi) {
            $attestedProblems = $attestedProblems->filter(function (AttestedProblem $attestedProblem) {
                $p = $attestedProblem->ccdProblem;
                $cpmProblem = $p->cpmProblem;
                if (is_null($cpmProblem)) {
                    return true;
                }

                return false == $cpmProblem->is_behavioral || in_array($cpmProblem->name, CpmProblem::DUAL_CCM_BHI_CONDITIONS);
            });
        }

        return $attestedProblems
            ->map(fn (AttestedProblem $attestedProblem) => $attestedProblem->ccdProblem)
            ->unique()->pluck('id');
    }

    public function setMonth(Carbon $month): AutoPatientAttestation
    {
        $this->month = $month;

        return $this;
    }

    public function setPmsId(?int $pmsId): AutoPatientAttestation
    {
        $this->pmsId = $pmsId;

        return $this;
    }

    public function unAttestedBhi(): bool
    {
        return $this->hasServiceCode(ChargeableService::BHI) && $this->getBhiAttestedProblems()->isEmpty();
    }

    public function unAttestedCcm(): bool
    {
        return $this->hasServiceCode(ChargeableService::CCM) && $this->getCcmAttestedProblems()->isEmpty();
    }

    public function unAttestedPcm(): bool
    {
        return $this->hasServiceCode(ChargeableService::PCM) && $this->getCcmAttestedProblems()->isEmpty();
    }

    private function attachProblems(array $problems)
    {
        if ( ! $this->isNewBillingEnabled()) {
            //todo: deprecate
            $this->patient->patientSummaryForMonth($this->month)
                ->attestedProblems()
                ->update(['call_problems.patient_monthly_summary_id' => null]);
        }

        (new AttestPatientProblems())
            ->problemsToAttest($problems)
            ->forMonth($this->month)
            ->forPms($this->pmsId)
            ->createRecords();
    }

    private function getBhiProblemsForAutoAttestation(): array
    {
        /** @var Problem $bhiProblem */
        $bhiProblem = $this->patientProblemsSortedByWeight()->filter(fn (Problem $p) => $p->isBehavioral())->first();

        if ( ! $bhiProblem) {
            return [];
        }

        return [
            [$bhiProblem->id],
        ];
    }

    private function getCcmProblemsForAutoAttestation(): array
    {
        $patientProblems     = $this->patientProblemsSortedByWeight();
        $ccmAttestedProblems = $this->getCcmAttestedProblems();

        return $ccmAttestedProblems
            ->merge(
                $patientProblems->filter(function (Problem $p) use ($ccmAttestedProblems) {
                    return ! $ccmAttestedProblems->contains('id', $p->id) && ! $p->isBehavioral();
                })
            )
            ->take(4)
            ->pluck('id')
            ->toArray();
    }

    private function hasServiceCode(string $code): bool
    {
        if ( ! $this->isNewBillingEnabled()) {
            /** @var PatientMonthlySummary $pms */
            $pms = $this->patient->patientSummaryForMonth($this->month);

            return boolval(optional($pms)->hasServiceCode($code));
        }

        return $this->patient->chargeableMonthlySummaries->where('code', $code)->isNotEmpty();
    }

    private function isNewBillingEnabled()
    {
        return Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
    }

    private function patientProblemsSortedByWeight(): Collection
    {
        $this->patient->loadMissing([
            'ccdProblems' => function ($problems) {
                $problems->with(['icd10codes', 'cpmProblem']);
            },
        ]);

        return $this->patient->ccdProblems->unique(function (Problem $p) {
            return $p->icd10Code();
        })
            ->sortByDesc(function ($problem) {
                if ( ! $problem->cpmProblem) {
                    return null;
                }

                return $problem->cpmProblem->weight;
            });
    }
}
