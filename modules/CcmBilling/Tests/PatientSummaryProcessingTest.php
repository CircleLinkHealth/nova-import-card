<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientProblemsChanged;
use CircleLinkHealth\CcmBilling\Events\PatientSuccessfulCallCreated;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Fake as FakeLocationRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Fake as FakePatientRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStatusDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceProcessorOutputDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientSummaryForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientTimeForProcessing;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Bus;

class PatientSummaryProcessingTest extends TestCase
{
    //todo:write plus code tests

    public function test_it_processes_patient_chargeable_services_at_the_start_of_month()
    {
        FakePatientRepository::fake();
        FakeLocationRepository::fake();

        $startOfMonth = Carbon::now()->startOfMonth()->startOfDay();

        $stub = (new PatientMonthlyBillingDTO())
            ->subscribe(AvailableServiceProcessors::push([new CCM(), new BHI()]))
            ->forPatient(1)
            ->ofLocation(1)
            ->setBillingStatus((new PatientMonthlyBillingStatusDTO())->setMonth($startOfMonth)->setActorId(null))
            ->forMonth($startOfMonth)
            ->withLocationServices(
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::CCM)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::BHI)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
            )
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

        $fakeProcessor = app(MonthlyProcessor::class);

        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getCcm()->code(), $startOfMonth);
        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getBhi()->code(), $startOfMonth);
    }

    public function test_it_respects_clashing_services()
    {
        FakePatientRepository::fake();

        $patientId    = 1;
        $locationId   = 1;
        $ccm          = new CCM();
        $pcm          = new PCM();
        $startOfMonth = Carbon::now()->startOfMonth()->startOfDay();

        $stub = (new PatientMonthlyBillingDTO())
            ->subscribe(AvailableServiceProcessors::push([$ccm, $pcm]))
            ->forPatient($patientId)
            ->ofLocation($locationId)
            ->forMonth($startOfMonth)
            ->setBillingStatus((new PatientMonthlyBillingStatusDTO())->setMonth($startOfMonth)->setActorId(null))
            ->withLocationServices(
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::CCM)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::PCM)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
            )
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

        $fakeProcessor = app(MonthlyProcessor::class);

        $fakeProcessor->process($stub);

        FakePatientRepository::assertChargeableSummaryCreated($patientId, $ccm->code(), $startOfMonth);
        FakePatientRepository::assertChargeableSummaryNotCreated($patientId, $pcm->code(), $startOfMonth);
    }

    public function test_it_sets_requires_patient_consent_when_it_should_and_stops_fulfilling()
    {
        $practice = factory(Practice::class)->create();
        $patient  = factory(User::class)->create([
            'program_id' => $practice->id,
        ]);

        AppConfig::updateOrCreate([
            'config_key'   => PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY,
            'config_value' => $practice->name,
        ]);

        $dto = (new PatientMonthlyBillingDTO())
            ->forPatient($patient->id)
            ->forMonth($startOfMonth = Carbon::now()->startOfMonth())
            ->ofLocation(1)
            ->setBillingStatus((new PatientMonthlyBillingStatusDTO())->setMonth($startOfMonth)->setActorId(5))
            ->withLocationServices(
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::CCM)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
                (new LocationChargeableServicesForProcessing())
                    ->setCode(ChargeableService::BHI)
                    ->setIsLocked(false)
                    ->setMonth($startOfMonth),
            )
            ->withPatientServices(
                (new PatientSummaryForProcessing())
                    ->setChargeableServiceId(ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI))
                    ->setRequiresConsent(1)
                    ->setCode(ChargeableService::BHI)
                    ->setIsFulfilled(false)
            )
            ->withPatientMonthlyTimes(
                (new PatientTimeForProcessing())
                    ->setChargeableServiceId(ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI))
                    ->setChargeableMonth($startOfMonth)
                    ->setTime(CpmConstants::TWENTY_MINUTES_IN_SECONDS)
            )
            ->withProblems(
                (new PatientProblemForProcessing())
                    ->setId(123)
                    ->setCode('1234')
                    ->setServiceCodes([
                        ChargeableService::BHI,
                    ]),
            );

        $processor = new BHI();
        /** @var PatientServiceProcessorOutputDTO */
        $output = $processor->processBilling($dto);

        self::assertFalse($output->isFulfilling());
    }

    public function test_job_to_process_patient_summaries_can_happen_once_every_five_minutes()
    {
        //todo: implemented debouncing using https://github.com/mpbarlow/laravel-queue-debouncer . Found it a bit hard to write unit test for.
    }

    public function test_listener_dispatches_jobs_to_process_when_it_should()
    {
        Bus::fake();

        event(new PatientProblemsChanged($patientId = 1));

        Bus::assertDispatched(function (ProcessSinglePatientMonthlyServices $job) use ($patientId) {
            return $job->getPatientId() === $patientId
                && $job->getMonth()->equalTo(Carbon::now()->startOfMonth()->startOfDay());
        });

        Bus::assertDispatchedTimes(ProcessSinglePatientMonthlyServices::class, 1);

        Bus::fake();

        event(new PatientSuccessfulCallCreated($patientId));

        Bus::assertDispatched(function (ProcessSinglePatientMonthlyServices $job) use ($patientId) {
            return $job->getPatientId() === $patientId
                && $job->getMonth()->equalTo(Carbon::now()->startOfMonth()->startOfDay());
        });

        Bus::assertDispatchedTimes(ProcessSinglePatientMonthlyServices::class, 1);
    }
}
