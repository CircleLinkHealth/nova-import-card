<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

class NurseCareRateLogsSeeder extends Seeder
{
    use UserHelpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice1 = $this->setupPractice(true);
        $provider1 = $this->createUser($practice1->id);
        $patient1  = $this->setupPatient($practice1);
        $practice2 = $this->setupPractice(false);
        $provider2 = $this->createUser($practice2->id);
        $patient2  = $this->setupPatient($practice2);
        $practice3 = $this->setupPractice(false);
        $provider3 = $this->createUser($practice3->id);
        $patient3  = $this->setupPatient($practice3);

        $nurseHourlyRate = 10.0;
        $nurse           = $this->setupNurse($practice1->id, true, $nurseHourlyRate, true);

        $nurseInfoId = $nurse->nurseInfo->id;
        $this->command->info("Ready to seed activities, calls and care rate logs for nurse[$nurse->id / $nurseInfoId]");

        /** @var \Carbon\Carbon $start */
        $start = Carbon::now()->startOfMonth()->startOfDay();

        $logsTarget = 10000;
        $dayDivider = $logsTarget / 30;

        for ($i = 0; $i < $logsTarget; ++$i) {
            if (0 === $i % 50) {
                $percent = round($i * 100 / $logsTarget);
                $this->command->info("Progress: $percent / 100");
            }

            if (0 === $i % 3) {
                $patient = $patient1;
            } elseif (1 === $i % 3) {
                $patient = $patient2;
            } else {
                $patient = $patient3;
            }

            $seconds = rand(5, 500);

            $day        = 0;
            $multiplier = $dayDivider * $day;
            while ($i > $multiplier) {
                ++$day;
                $multiplier = $dayDivider * $day;
            }

            $billable           = 1; //rand(0, 1);
            $withSuccessfulCall = rand(0, 1);
            $when               = $start->copy()->addDay($day);

            $this->addTime($nurse, $patient, $seconds, 1 === $billable, 1 === $withSuccessfulCall, $when);
        }

        $this->command->info('Done');
    }

    private function addTime(
        User $nurse,
        User $patient,
        int $seconds,
        bool $billable,
        bool $withSuccessfulCall = false,
        Carbon\Carbon $when = null
    ) {
        if ($withSuccessfulCall) {
            /** @var Note $fakeNote */
            $fakeNote             = \factory(Note::class)->make();
            $fakeNote->author_id  = $nurse->id;
            $fakeNote->patient_id = $patient->id;
            $fakeNote->status     = Note::STATUS_COMPLETE;
            $fakeNote->save();

            /** @var Call $fakeCall */
            $fakeCall                  = \factory(Call::class)->make();
            $fakeCall->note_id         = $fakeNote->id;
            $fakeCall->status          = Call::REACHED;
            $fakeCall->inbound_cpm_id  = $patient->id;
            $fakeCall->outbound_cpm_id = $nurse->id;
            $fakeCall->save();
        }

        $bag = new ParameterBag();
        $bag->add([
            'providerId' => $nurse->id,
            'patientId'  => $billable
                ? $patient->id
                : 0,
            'activities' => [
                [
                    'is_behavioral' => false,
                    'duration'      => $seconds,
                    'start_time'    => $when ?? Carbon::now(),
                    'name'          => $withSuccessfulCall
                        ? 'Patient Note Creation'
                        : 'test',
                    'title'     => 'test',
                    'url'       => 'test',
                    'url_short' => 'test',
                ],
            ],
        ]);
        (new StoreTimeTracking($bag))->handle();
    }

    private function setupNurse(
        int $practiceId,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = null
    ) {
        $nurse                              = $this->createUser($practiceId, 'care-center');
        $nurse->nurseInfo->is_variable_rate = $variableRate;
        $nurse->nurseInfo->hourly_rate      = $hourlyRate;
        $nurse->nurseInfo->high_rate        = 30.00;
        $nurse->nurseInfo->high_rate_2      = 28.00;
        $nurse->nurseInfo->high_rate_3      = 27.50;

        $nurse->nurseInfo->low_rate = 10;

        if ($visitFee) {
            $nurse->nurseInfo->visit_fee   = $visitFee;
            $nurse->nurseInfo->visit_fee_2 = 12.00;
            $nurse->nurseInfo->visit_fee_3 = 11.75;
        }

        $nurse->nurseInfo->save();

        AppConfig::set(NurseCcmPlusConfig::NURSE_CCM_PLUS_ENABLED_FOR_ALL, $enableCcmPlus
                    ? 'true'
                    : 'false');

        if ($enableCcmPlus && $visitFee) {
            $current = implode(',', NurseCcmPlusConfig::altAlgoEnabledForUserIds());
            AppConfig::set(NurseCcmPlusConfig::NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_USER_IDS, $current.(empty($current)
                            ? ''
                            : ',').$nurse->id);
        }

        return $nurse;
    }

    private function setupPatient(Practice $practice)
    {
        $patient    = $this->createUser($practice->id, 'participant');
        $locationId = Location::firstOrCreate([
            'practice_id' => $practice->id,
        ])->id;
        $patient->setPreferredContactLocation($locationId);
        $patient->patientInfo->save();
        $cpmProblems = CpmProblem::get();
        $ccdProblems = $patient->ccdProblems()->createMany([
            ['name' => 'test'.Str::random(5)],
            ['name' => 'test'.Str::random(5)],
            ['name' => 'test'.Str::random(5)],
        ]);
        foreach ($ccdProblems as $problem) {
            $problem->cpmProblem()->associate($cpmProblems->random());
            $problem->save();
        }

        return $patient;
    }

    private function setupPractice(bool $addCcmPlusServices = false)
    {
        $practice              = factory(Practice::class)->create();
        $ccmService            = ChargeableService::where('code', '=', ChargeableService::CCM)->first();
        $sync                  = [];
        $sync[$ccmService->id] = ['amount' => 29.0];
        if ($addCcmPlusServices) {
            $ccmPlus40            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_40)->first();
            $ccmPlus60            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_60)->first();
            $sync[$ccmPlus40->id] = ['amount' => 28.0];
            $sync[$ccmPlus60->id] = ['amount' => 28.0];
        }

        $practice->chargeableServices()->sync($sync);

        return $practice;
    }
}
