<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use App\Constants;
use App\Events\PatientUserCreated;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientActivityCreated;
use CircleLinkHealth\CcmBilling\Events\PatientProblemsChanged;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM40;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Fake as FakePatientRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsAttachedStub;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs\IsFulfilledStub;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PatientSummaryProcessingTest extends TestCase
{
    public function test_it_does_not_attach_next_service_in_sequence_if_previous_is_not_fulfilled()
    {
        FakePatientRepository::fake();

        $patientId = 1;
        $ccm       = new CCM();
        $ccm40     = new CCM40();
        $month     = now();

        FakePatientRepository::setIsAttachedStubs(
            new IsAttachedStub($patientId, $ccm->code(), $month, false)
        );

        FakePatientRepository::setIsFulfilledStubs(
            new IsFulfilledStub($patientId, $ccm->code(), $month, false)
        );

        $stub = (new PatientMonthlyBillingStub())
            ->subscribe(AvailableServiceProcessors::push([$ccm, $ccm40]))
            ->forPatient($patientId)
            ->forMonth($month)
            ->withProblems(
                (new PatientProblemForProcessing())
                    ->setId(123)
                    ->setCode('1234')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::CCM_PLUS_40,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1233)
                    ->setCode('12344')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1235)
                    ->setCode('12345')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::CCM_PLUS_40,
                    ])
            );

        $fakeProcessor = new MonthlyProcessor();

        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated($patientId, $ccm->code(), $month);
        FakePatientRepository::assertChargeableSummaryNotCreated($patientId, $ccm40->code(), $month);

        FakePatientRepository::setIsFulfilledStubs(
            new IsFulfilledStub($patientId, $ccm->code(), $month, true)
        );

        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated($patientId, $ccm40->code(), $month);
    }

    public function test_it_does_not_fulfill_if_service_requires_patient_consent()
    {
    }

    public function test_it_only_attaches_next_service_if_it_is_enabled_for_location_for_month()
    {
        $patientId = 1;
        $month     = now();

        FakePatientRepository::fake();

        $processor = new CCM();

        FakePatientRepository::setIsChargeableServiceEnabledForMonth(false);
        $processor->attachNext($patientId, $month);
        FakePatientRepository::assertChargeableSummaryNotCreated($patientId, $processor->next()->code(), $month);

        FakePatientRepository::setIsChargeableServiceEnabledForMonth(true);
        $processor->attachNext($patientId, $month);
        FakePatientRepository::assertChargeableSummaryCreated($patientId, $processor->next()->code(), $month);
    }

    public function test_it_processes_patient_chargeable_services_at_the_start_of_month()
    {
        FakePatientRepository::fake();

        $stub = (new PatientMonthlyBillingStub())
            ->subscribe(AvailableServiceProcessors::push([new CCM(), new BHI()]))
            ->forPatient(1)
            ->forMonth($startOfMonth = Carbon::now()->startOfMonth()->startOfDay())
            ->withProblems(
                (new PatientProblemForProcessing())
                    ->setId(123)
                    ->setCode('1234')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::BHI,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1233)
                    ->setCode('12344')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1235)
                    ->setCode('12345')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::BHI,
                    ])
            );

        $fakeProcessor = new MonthlyProcessor();

        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getCcm()->code(), $startOfMonth);
        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getBhi()->code(), $startOfMonth);
    }

    public function test_it_respects_clashing_services()
    {
        FakePatientRepository::fake();

        $patientId = 1;
        $ccm       = new CCM();
        $pcm       = new PCM();
        $month     = now();

        $stub = (new PatientMonthlyBillingStub())
            ->subscribe(AvailableServiceProcessors::push([$ccm, $pcm]))
            ->forPatient($patientId)
            ->forMonth($month)
            ->withProblems(
                (new PatientProblemForProcessing())
                    ->setId(123)
                    ->setCode('1234')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::PCM,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1233)
                    ->setCode('12344')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                    ]),
                (new PatientProblemForProcessing())
                    ->setId(1235)
                    ->setCode('12345')
                    ->setServiceCodes([
                        ChargeableService::CCM,
                        ChargeableService::PCM,
                    ])
            );

        $fakeProcessor = new MonthlyProcessor();
        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated($patientId, $ccm->code(), $month);
        FakePatientRepository::assertChargeableSummaryNotCreated($patientId, $pcm->code(), $month);
    }

    public function test_it_sets_requires_patient_consent_when_it_should()
    {
    }

    public function test_job_to_process_patient_summaries_can_happen_once_every_five_minutes()
    {
        //todo: implemented debouncing using https://github.com/mpbarlow/laravel-queue-debouncer . Found it a bit hard to write unit test for.
    }

    public function test_listerner_dispatches_jobs_to_process_when_it_should()
    {
        Bus::fake();

        event(new PatientUserCreated($user = factory(User::class)->make(['id' => $patientId = 1])));

        Bus::assertDispatched(function (ProcessSinglePatientMonthlyServices $job) use ($patientId) {
            return $job->getPatientId() === $patientId
                && $job->getMonth()->equalTo(Carbon::now()->startOfMonth()->startOfDay());
        });

        Bus::assertDispatchedTimes(ProcessSinglePatientMonthlyServices::class, 1);

        Bus::fake();

        event(new PatientProblemsChanged($patientId));

        Bus::assertDispatched(function (ProcessSinglePatientMonthlyServices $job) use ($patientId) {
            return $job->getPatientId() === $patientId
                && $job->getMonth()->equalTo(Carbon::now()->startOfMonth()->startOfDay());
        });

        Bus::assertDispatchedTimes(ProcessSinglePatientMonthlyServices::class, 1);
    }
}
