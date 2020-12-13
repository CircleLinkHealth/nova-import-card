<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Algorithms;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use CircleLinkHealth\Nurseinvoices\Debug\MeasureTime;
use CircleLinkHealth\NurseInvoices\ValueObjects\CalculationResult;
use CircleLinkHealth\NurseInvoices\ValueObjects\PatientPayCalculationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariablePayCalculator
{
    protected bool $debug = false;
    protected Carbon $endDate;

    protected array $nurseInfoIds;

    protected Carbon $startDate;

    /**
     * Cache variable, holds care rate logs.
     */
    private ?Collection $nurseCareRateLogs = null;

    public function __construct(array $nurseInfoIds, Carbon $startDate, Carbon $endDate, bool $debug = false)
    {
        $this->debug        = $debug;
        $this->nurseInfoIds = $nurseInfoIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    /**
     * @throws \Exception        when patient not found
     * @return CalculationResult
     */
    public function calculate(User $nurse)
    {
        return $this->measureTimeAndLog("$nurse->id-calculate", function () use ($nurse) {
            $nurseInfo = $nurse->nurseInfo;
            $careRateLogs = $this->measureTimeAndLog(
                "$nurse->id-careRateLogs",
                fn () => $this->getForNurses()
            );

            $totalPay = 0.0;
            $visitsPerPatientPerChargeableServiceCode = collect();
            $ccmPlusAlgoEnabled = $this->isNewNursePayAlgoEnabled();
            $visitFeeBased = $ccmPlusAlgoEnabled && $this->isNewNursePayAltAlgoEnabledForUser($nurse->id);

            $perPatient = $careRateLogs->mapToGroups(function ($e) {
                return [$e['patient_user_id'] => $e];
            });

            $perPatient->each(function (Collection $patientCareRateLogs) use (
                &$totalPay,
                $visitsPerPatientPerChargeableServiceCode,
                $nurseInfo,
                $ccmPlusAlgoEnabled,
                $visitFeeBased
            ) {
                $patientUserIdStr = optional($patientCareRateLogs->first())->patient_user_id ?? '';
                $patientPayCalculation = $this->measureTimeAndLog(
                    "$patientUserIdStr-calculateBasedOnAlgorithm",
                    fn () => $this->calculateBasedOnAlgorithm($nurseInfo, $patientCareRateLogs, $ccmPlusAlgoEnabled, $visitFeeBased)
                );

                $totalPay += $patientPayCalculation->pay;

                if (optional($patientPayCalculation->visitsPerChargeableServiceCode)->isNotEmpty()) {
                    $patientUserId = $patientCareRateLogs->first()->patient_user_id;
                    $visitsPerPatientPerChargeableServiceCode->put($patientUserId, $patientPayCalculation->visitsPerChargeableServiceCode);
                }
            });

            return new CalculationResult(
                $ccmPlusAlgoEnabled,
                $visitFeeBased,
                $visitsPerPatientPerChargeableServiceCode,
                $totalPay
            );
        });
    }

    public function getForNurses()
    {
        if ($this->nurseCareRateLogs) {
            return $this->nurseCareRateLogs;
        }

        $nurseCareRateLogTable   = (new NurseCareRateLog())->getTable();
        $nurseInfoTable          = (new Nurse())->getTable();
        $this->nurseCareRateLogs = NurseCareRateLog
            ::select(["$nurseCareRateLogTable.*", "$nurseInfoTable.start_date"])
                ->leftJoin($nurseInfoTable, "$nurseInfoTable.id", '=', "$nurseCareRateLogTable.nurse_id")
                ->whereIn("$nurseCareRateLogTable.patient_user_id", function ($query) use ($nurseCareRateLogTable) {
                    $query->select('patient_user_id')
                        ->from($nurseCareRateLogTable)
                        ->whereIn('nurse_id', $this->nurseInfoIds)
                        ->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->groupBy('patient_user_id');
                })
                ->whereBetween("$nurseCareRateLogTable.created_at", [$this->startDate, $this->endDate])
                ->where(function ($q) use ($nurseCareRateLogTable, $nurseInfoTable) {
                    $q->whereNull("$nurseInfoTable.start_date")
                        ->orWhere(DB::raw("DATE($nurseCareRateLogTable.performed_at)"), '>=', DB::raw("DATE($nurseInfoTable.start_date)"));
                })
                ->get();

        return $this->nurseCareRateLogs;
    }

    private function calculateBasedOnAlgorithm(Nurse $nurseInfo, Collection $patientCareRateLogs, bool $ccmPlusAlgoEnabled, bool $visitFeeBased): PatientPayCalculationResult
    {
        $patientUserId = $patientCareRateLogs->first()->patient_user_id;
        if ( ! $patientUserId) {
            return (new LegacyPaymentAlgorithm(
                $nurseInfo,
                $patientCareRateLogs,
                $this->startDate,
                $this->endDate,
                null
            ))->calculate();
        }

        /** @var User $patient */
        $patient = $this->measureTimeAndLog(
            "$patientUserId-find",
            function () use ($patientUserId) {
                return User::withTrashed()
                    ->with([
                        'primaryPractice.chargeableServices',
                    ])
                    ->find($patientUserId);
            }
        );

        if ( ! $patient) {
            Log::critical("Could not find user with id $patientUserId");

            return PatientPayCalculationResult::withVisits(collect());
        }

        if ($patient->primaryPractice->is_demo) {
            return PatientPayCalculationResult::withVisits(collect());
        }

        $options = [$nurseInfo, $patientCareRateLogs, $this->startDate, $this->endDate, $patient, $this->debug];

        if ( ! $ccmPlusAlgoEnabled) {
            return (new LegacyPaymentAlgorithm(...$options))->calculate();
        }

        if ( ! $visitFeeBased) {
            /** @var bool $patientIsPcm */
            $patientIsPcm = $this->measureTimeAndLog(
                "$patientUserId-isPcm",
                fn () => $patient->isPcm()
            );
            $visitFeeBased = $patientIsPcm;
        }

        if ($visitFeeBased) {
            return $this->measureTimeAndLog(
                "$patientUserId-VisitCalculate",
                fn () => (new VisitFeePaymentAlgorithm(...$options))->calculate()
            );
        }

        return (new VariableRatePaymentAlgorithm(...$options))->calculate();
    }

    private function isNewNursePayAlgoEnabled()
    {
        return NurseCcmPlusConfig::enabledForAll();
    }

    private function isNewNursePayAltAlgoEnabledForUser(
        $nurseUserId
    ) {
        if (NurseCcmPlusConfig::altAlgoEnabledForAll()) {
            return true;
        }

        $enabledForUserIds = NurseCcmPlusConfig::altAlgoEnabledForUserIds();
        if ($enabledForUserIds) {
            return in_array($nurseUserId, $enabledForUserIds);
        }

        return false;
    }

    private function measureTimeAndLog(string $desc, $func)
    {
        if ( ! $this->debug) {
            return $func();
        }

        $msg = "VariablePayCalculator-$desc";

        return MeasureTime::log($msg, $func);
    }
}
