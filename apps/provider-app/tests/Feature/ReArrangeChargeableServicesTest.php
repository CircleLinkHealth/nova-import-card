<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Console\Commands\ReArrangeActivityChargeableServices;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Jobs\ProcessMonthltyPatientTime;
use CircleLinkHealth\Customer\Jobs\ProcessNurseMonthlyLogs;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\NurseInvoices\Algorithms\VisitFeePaymentAlgorithm;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeRangeEntry;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\DTO\CreatePageTimerParams;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Services\PageTimerService;
use Illuminate\Support\Collection;
use ReflectionMethod;
use CircleLinkHealth\Core\Tests\TestCase;

class ReArrangeChargeableServicesTest extends TestCase
{
    use PracticeHelpers;
    use UserHelpers;

    /**
     * sum: 1206
     * last ccm activity: 6 seconds
     * no ccm40 activity.
     */
    public function test_activity_changes_chargeable_service_id()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1200, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 6, $csId);

        $this->validateSummariesData($patient->id, 1206, 0);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 6);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);
    }

    /**
     * sum: 1206
     * last ccm activity: 6 seconds
     * has ccm40 activity.
     */
    public function test_activity_changes_chargeable_service_id_2()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1200, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 6, $csId);

        $ccm40CsId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM_PLUS_40)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 100, $ccm40CsId);

        $this->validateSummariesData($patient->id, 1206, 100);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 106);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 106);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 106);
    }

    /**
     * sum: 2406
     * last activity: 1306
     * no ccm40 or ccm60 activity.
     */
    public function test_it_creates_new_activities_for_chargeable_service_ids()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1100, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1106, $csId);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(2, $count);

        $this->validateSummariesData($patient->id, 2206, 0, 0);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 1006, 0);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 1006, 0);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 1006, 0);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(3, $count);
    }

    /**
     * sum: 1206
     * last activity: 4 seconds
     * no ccm40 activity.
     */
    public function test_it_creates_new_activity_and_sets_chargeable_service_id()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1100, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 102, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 4, $csId);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(3, $count);

        $this->validateSummariesData($patient->id, 1206, 0);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 6);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(4, $count);
    }

    /**
     * sum: 1206
     * last activity: 106 seconds
     * no ccm40 activity.
     */
    public function test_it_creates_new_activity_for_chargeable_service_id()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1100, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 106, $csId);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(2, $count);

        $this->validateSummariesData($patient->id, 1206, 0);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 6);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 6);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(3, $count);
    }

    public function test_new_activity_matches_nurse_care_rate_log()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->createUser($practice->id, 'care-center');
        $patient  = $this->setupPatient($practice);

        $csId   = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $cs40Id = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM_PLUS_40)->id;
        $this->addActivity($patient->id, $practice->id, $nurse->id, 1100, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 306, $csId);
        $this->addActivity($patient->id, $practice->id, $nurse->id, 400, $cs40Id);

        $this->validateSummariesData($patient->id, 1406, 400);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 606);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(3, $count);

        $this->artisan(ReArrangeActivityChargeableServices::class, [
            'month' => now()->startOfMonth()->toDateString(),
        ]);

        $this->validateSummariesData($patient->id, 1200, 606);
        $this->validateNurseCareRateLogs($patient, $nurse->id, 1200, 606);

        $count = Activity::where('patient_id', '=', $patient->id)->count();
        self::assertEquals(4, $count);
    }

    private function addActivity(int $patientId, int $practiceId, int $providerId, int $duration, int $csId)
    {
        $pageTimerService        = app(PageTimerService::class);
        $patientServiceProcessor = app(PatientServiceProcessorRepository::class);
        $params                  = (new CreatePageTimerParams())
            ->setActivity([
                'duration'              => $duration,
                'start_time'            => now()->format('Y-m-d H:i:s'),
                'end_time'              => now()->addSeconds($duration)->format('Y-m-d H:i:s'),
                'chargeable_service_id' => $csId,
                'enrolleeId'            => '',
                'url'                   => 'test',
                'url_short'             => 'test',
                'name'                  => 'test',
                'title'                 => 'test',
            ])
            ->setPatientId($patientId)
            ->setProgramId($practiceId)
            ->setProviderId($providerId);
        $pageTimer = $pageTimerService->createPageTimer($params);

        $chargeableServiceDuration = new ChargeableServiceDuration($csId, $duration, false);
        $activity                  = $patientServiceProcessor->createActivityForChargeableService('test', $pageTimer, $chargeableServiceDuration);
        ProcessMonthltyPatientTime::dispatchNow($patientId);
        ProcessNurseMonthlyLogs::dispatchNow($activity);
        event(new PatientActivityCreated($patientId));
    }

    private function validateNurseCareRateLogs(User $patient, int $providerId, int $timeCcm, int $timeCcm40, int $timeCcm60 = 0)
    {
        $careRateLogs = NurseCareRateLog::where('patient_user_id', '=', $patient->id)
            ->whereBetween('performed_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->get();

        $method = new ReflectionMethod(VisitFeePaymentAlgorithm::class, 'separateTimeAccruedInRanges');
        $method->setAccessible(true);

        /** @var Nurse $nurseInfo */
        $nurseInfo = Nurse::whereUserId($providerId)->first();

        /** @var Collection $result */
        $result = $method->invoke(new VisitFeePaymentAlgorithm(
            $nurseInfo,
            $careRateLogs,
            now()->startOfMonth(),
            now()->endOfMonth(),
            $patient
        ), $careRateLogs);

        // care rate logs only for one cs code (CCM)
        self::assertEquals(1, $result->count());

        /** @var Collection $ccmCollection */
        $ccmCollection = $result->first();
        // sub-collection for 2 or 3 ccm codes (CCM, CCM40, CCM60)
        self::assertEquals($timeCcm60 > 0 ? 3 : 2, $ccmCollection->count());

        /** @var Collection $ccm20Collection */
        $ccm20Collection = $ccmCollection->get(0);

        // ccm 20 only for one nurse
        self::assertEquals(1, $ccm20Collection->count());

        /** @var TimeRangeEntry $timeRange */
        $timeRange = $ccm20Collection->first();
        self::assertEquals($timeCcm, $timeRange->duration);

        $ccm40Collection = $ccmCollection->get(1);

        // ccm 40 only for one nurse
        self::assertEquals(1, $ccm40Collection->count());

        /** @var TimeRangeEntry $timeRange */
        $timeRange = $ccm40Collection->first();
        self::assertEquals($timeCcm40, $timeRange->duration);

        if ($timeCcm60) {
            $ccm60Collection = $ccmCollection->get(2);

            // ccm 40 only for one nurse
            self::assertEquals(1, $ccm60Collection->count());

            /** @var TimeRangeEntry $timeRange */
            $timeRange = $ccm60Collection->first();
            self::assertEquals($timeCcm60, $timeRange->duration);
        }
    }

    private function validateSummariesData(int $patientId, int $timeCcm, int $timeCcm40, int $timeCcm60 = 0)
    {
        $service = app(PatientServiceProcessorRepository::class);
        $service->reloadPatientSummaryViews($patientId, now()->startOfMonth());

        $csId      = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM)->id;
        $ccm40CsId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM_PLUS_40)->id;
        $ccm60CsId = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::CCM_PLUS_60)->id;

        $summaries = $service->getChargeablePatientSummaries($patientId, now()->startOfMonth());
        self::assertEquals(3, $summaries->count());

        /** @var ChargeablePatientMonthlySummaryView $ccmSummary */
        $ccmSummary = $summaries->firstWhere('chargeable_service_code', '=', ChargeableService::CCM);
        self::assertEquals($timeCcm, $ccmSummary->total_time);
        self::assertEquals($csId, $ccmSummary->chargeable_service_id);

        /** @var ChargeablePatientMonthlySummaryView $ccm40Summary */
        $ccm40Summary = $summaries->firstWhere('chargeable_service_code', '=', ChargeableService::CCM_PLUS_40);
        self::assertEquals($timeCcm40, $ccm40Summary->total_time);
        self::assertEquals($ccm40CsId, $ccm40Summary->chargeable_service_id);

        /** @var ChargeablePatientMonthlySummaryView $ccm40Summary */
        $ccm60Summary = $summaries->firstWhere('chargeable_service_code', '=', ChargeableService::CCM_PLUS_60);
        self::assertEquals($timeCcm60, $ccm60Summary->total_time);
        self::assertEquals($ccm60CsId, $ccm60Summary->chargeable_service_id);
    }
}
