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

        $practice = Practice::find($practiceId);
        $this->setupExistingPractice($practice, true, true, true, true, true);
        $practice->locations->each(fn (Location $l) => GenerateLocationSummaries::dispatchNow($l->id));

        $nurse = $this->createUser($practice->id, 'care-center');
        $this->setupNurse($nurse, true, 29.0, true, 12.50);

        for ($i = 0; $i < $count; ++$i) {
            $this->createPatient($practice, $nurse);
        }

        BillingCache::clearPatients();
        ProcessPracticePatientMonthlyServices::dispatchNow($practiceId);

        $this->info('Done');

        return 0;
    }

    private function createNoteAndCall(User $author, User $patient)
    {
        auth()->loginUsingId($author->id);
        session()->regenerateToken();
        $args = [
            '_token'      => session()->token(),
            'body'        => 'test',
            'patient_id'  => $patient->id,
            'phone'       => 1,
            'call_status' => Call::REACHED,
        ];

        $request = Request::create(route('patient.note.store', ['patientId' => $patient->id]), 'POST', $args);
        app()->handle($request);
        auth()->logout();
    }

    private function createPatient(Practice $practice, User $nurse)
    {
        $patient = $this->setupPatient($practice, false, true, false, false);
        $this->createNoteAndCall($nurse, $patient);

        $chargeableServiceId = ChargeableService::firstWhere('code', '=', ChargeableService::CCM)->id;
        $this->storeTime($nurse, $patient, 21, $chargeableServiceId);

        $this->info("Created patient[$patient->id]");
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
