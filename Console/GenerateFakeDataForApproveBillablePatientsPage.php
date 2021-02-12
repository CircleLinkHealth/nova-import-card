<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use App\Call;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class GenerateFakeDataForApproveBillablePatientsPage extends Command
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake data for ABP';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-data:abp {practiceId} {numberOfPatients}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $practiceId = $this->argument('practiceId');

        $count = intval($this->argument('numberOfPatients'));

        $practice = measureTime('practice', function () use ($practiceId) {
            $practice = Practice::find($practiceId);
            $this->setupExistingPractice($practice, true, true, true, true, true, true);
            $practice->locations->each(fn (Location $l) => GenerateLocationSummaries::dispatchNow($l->id));

            return $practice;
        });

        measureTime('provider', function () use ($practiceId) {
            return $this->createUser($practiceId, 'provider');
        });

        $nurse = measureTime('nurse', function () use ($practiceId) {
            $nurse = $this->createUser($practiceId, 'care-center');
            $this->setupNurse($nurse, true, 29.0, true, 12.50);

            return $nurse;
        });

        for ($i = 0; $i < $count; ++$i) {
            echo "---------------------\n";
            measureTime('patient'.($i + 1), function () use ($practice, $nurse) {
                $this->createPatient($practice, $nurse);
            });
            echo "\n";
        }

        measureTime('processing', function () use ($practiceId) {
            BillingCache::clearPatients();
            ProcessPracticePatientMonthlyServices::dispatchNow($practiceId);
        });

        $this->info('Done');

        return 0;
    }

    private function createNoteAndCall(User $author, User $patient)
    {
        measureTime('login', function () use ($author) {
            auth()->loginUsingId($author->id);
            session()->regenerateToken();
        });

        measureTime('request', function () use ($patient) {
            $args = [
                '_token'      => session()->token(),
                'body'        => 'test',
                'patient_id'  => $patient->id,
                'phone'       => 1,
                'call_status' => Call::REACHED,
            ];

            $request = Request::create(route('patient.note.store', ['patientId' => $patient->id]), 'POST', $args);
            app()->handle($request);
        });

        measureTime('logout', fn () => auth()->logout());
    }

    private function createPatient(Practice $practice, User $nurse)
    {
        $isPcm = (bool) rand(0, 1);
        $isBhi = $isPcm ? false : (bool) rand(0, 1);
        $isRpm = $isPcm ? false : (bool) rand(0, 1);
        $isRhc = $isPcm ? false : (bool) rand(0, 1);

        $patient = $this->setupPatient($practice, $isBhi, $isPcm, $isRpm, false, false);
        measureTime('createNoteAndCall', fn () => $this->createNoteAndCall($nurse, $patient));

        $services = [];
        if ($isPcm) {
            $services[]          = 'PCM';
            $chargeableServiceId = ChargeableService::cached()
                ->firstWhere('code', '=', ChargeableService::PCM)->id;

            $this->storeAndMeasureTime($nurse, $patient, 33, $chargeableServiceId);
        } else {
            $services[]          = 'CCM';
            $chargeableServiceId = ChargeableService::cached()
                ->firstWhere('code', '=', ChargeableService::CCM)->id;

            $this->storeAndMeasureTime($nurse, $patient, 45, $chargeableServiceId);
        }

        if ($isBhi) {
            $services[]          = 'BHI';
            $chargeableServiceId = ChargeableService::cached()
                ->firstWhere('code', '=', ChargeableService::BHI)->id;

            $this->storeAndMeasureTime($nurse, $patient, 23, $chargeableServiceId);
        }

        if ($isRpm) {
            $services[]          = 'RPM';
            $chargeableServiceId = ChargeableService::cached()
                ->firstWhere('code', '=', ChargeableService::RPM)->id;

            $this->storeAndMeasureTime($nurse, $patient, 24, $chargeableServiceId);
        }

        if ($isRhc) {
            $services[]          = 'RHC';
            $chargeableServiceId = ChargeableService::cached()
                ->firstWhere('code', '=', ChargeableService::GENERAL_CARE_MANAGEMENT)->id;

            $this->storeAndMeasureTime($nurse, $patient, 24, $chargeableServiceId);
        }

        $str = implode(',', $services);
        $this->info("Created patient[$patient->id] with conditions: $str");
    }

    private function storeAndMeasureTime(User $nurse, User $patient, int $minutes, int $chargeableServiceId)
    {
        measureTime('storeTime', function () use ($nurse, $patient, $minutes, $chargeableServiceId) {
            if (app()->runningUnitTests()) {
                Event::fakeFor(function () use ($nurse, $patient, $minutes, $chargeableServiceId) {
                    $this->storeTime($nurse, $patient, $minutes, $chargeableServiceId);
                });
            } else {
                $this->storeTime($nurse, $patient, $minutes, $chargeableServiceId);
            }
        });
    }

    private function storeTime(User $nurse, User $patient, int $minutes, int $chargeableServiceId = null)
    {
        if ( ! $chargeableServiceId) {
            $chargeableServiceId = ChargeableService::firstWhere('code', '=', ChargeableService::CCM)->id;
        }

        $seconds = $minutes * 60;
        $bag     = new ParameterBag();
        $bag->add([
            'providerId' => $nurse->id,
            'patientId'  => $patient->id,
            'activities' => [
                [
                    'chargeable_service_id' => $chargeableServiceId,
                    'duration'              => $seconds,
                    'start_time'            => $startTime ?? now(),
                    'name'                  => 'Patient Note Creation',
                    'title'                 => 'test',
                    'url'                   => 'test',
                    'url_short'             => 'test',
                    'enrolleeId'            => 0,
                    'force_skip'            => false,
                ],
            ],
        ]);

        StoreTimeTracking::dispatchNow($bag);
    }
}
