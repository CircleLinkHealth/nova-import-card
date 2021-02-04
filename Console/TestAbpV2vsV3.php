<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestAbpV2vsV3 extends Command
{
    private const PATIENT_PROPS_TO_CHECK = [
        'mrn',
        'name',
        'url',
        'provider',
        'practice',
        'practice_id',
        'dob',
        'ccm',
        'total_time',
        'bhi_time',
        'ccm_time',
        'no_of_successful_calls',
        'status',
        'approve',
        'reject',
        'report_id',
        'actor_id',
        'qa',
        'problems',
        'attested_ccm_problems',
        'chargeable_services',
        'attested_bhi_problems',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test data integrity between ABP versions.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:abp-versions {practiceId} {month?}';

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
     * @return mixed
     */
    public function handle()
    {
        $practiceId = $this->argument('practiceId');
        $month      = $this->argument('month');
        $month      = ($month ? Carbon::parse($month) : now())->startOfMonth();

        $jsoned = measureTime('v2', function () use ($practiceId, $month) {
            $service = app(ApproveBillablePatientsService::class);
            $results = $service->getBillablePatientsForMonth($practiceId, $month);

            return json_decode(json_encode($results), true);
        });

        $v3Jsoned = measureTime('v3', function () use ($practiceId, $month) {
            $serviceV3 = app(ApproveBillablePatientsServiceV3::class);
            $v3Results = $serviceV3->getBillablePatientsForMonth($practiceId, $month);

            return json_decode(json_encode($v3Results), true);
        });

        $this->checkIsClosed($jsoned, $v3Jsoned);
        $checkMore = $this->checkDataCount($jsoned, $v3Jsoned);
        if ($checkMore) {
            $this->checkPatientIdsRetrieved($jsoned, $v3Jsoned);
            $this->checkPatients($jsoned, $v3Jsoned);
        }

        $this->newLine();
        $this->info('Done');

        return 0;
    }

    private function checkArrayProps(array $old, array $new): bool
    {
        foreach ($old as $key => $oldValue) {
            if ( ! isset($new[$key])) {
                return false;
            }

            $newValue = $new[$key];
            if (is_array($oldValue) && ! is_array($newValue)) {
                return false;
            }

            if ( ! is_array($oldValue) && is_array($newValue)) {
                return false;
            }

            if (is_array($oldValue) && ! $this->checkArrayProps($oldValue, $newValue)) {
                return false;
            }

            if ($oldValue !== $newValue) {
                return false;
            }
        }

        return true;
    }

    private function checkDataCount(array $old, array $new): bool
    {
        $countEquals = $old['summaries']['total'] === $new['summaries']['total'];
        $this->printResult('count', $countEquals);

        return $countEquals;
    }

    private function checkIsClosed(array $old, array $new)
    {
        $isClosedEquals = $old['isClosed'] === $new['isClosed'];
        $this->printResult('isClosed', $isClosedEquals);
    }

    private function checkPatient(array $oldP, array $newP): array
    {
        $results = [$oldP['id']];
        collect(self::PATIENT_PROPS_TO_CHECK)
            ->each(function ($prop) use ($oldP, $newP, &$results) {
                $results[] = $this->checkPatientProperty($oldP, $newP, $prop);
            });

        return $results;
    }

    private function checkPatientIdsRetrieved(array $old, array $new)
    {
        $oldPatients = $old['summaries']['data'];
        $newPatients = $new['summaries']['data'];

        $allMatch = collect($oldPatients)->every(function ($oldP, $index) use ($newPatients) {
            $newP = $newPatients[$index];

            return $oldP['id'] === $newP['id'];
        });
        $this->printResult('matchingPatientIds', $allMatch);
    }

    private function checkPatientProperty(array $oldP, array $newP, string $prop): string
    {
        if (isset($oldP[$prop]) && ! isset($newP[$prop])) {
            return $this->errorLog('x');
        }

        if ( ! isset($oldP[$prop]) && isset($newP[$prop])) {
            return $this->errorLog('x');
        }

        if (is_array($oldP[$prop]) && ! is_array($newP[$prop])) {
            return $this->errorLog('x');
        }

        if ( ! is_array($oldP[$prop]) && is_array($newP[$prop])) {
            return $this->errorLog('x');
        }

        if (is_array($oldP[$prop])) {
            return $this->checkArrayProps($oldP[$prop], $newP[$prop]) ? $this->successLog('1') : $this->errorLog('x');
        }

        return $oldP[$prop] === $newP[$prop] ? $this->successLog('1') : $this->errorLog('x');
    }

    private function checkPatients(array $old, array $new)
    {
        $oldPatients = $old['summaries']['data'];
        $newPatients = $new['summaries']['data'];
        $results     = [];
        collect($oldPatients)->each(function ($oldP, $index) use ($newPatients, &$results) {
            $newP = $newPatients[$index];
            $results[] = $this->checkPatient($oldP, $newP);
        });
        $this->table(['id', ...self::PATIENT_PROPS_TO_CHECK], $results);
    }

    private function errorLog($log)
    {
        return "<fg=red>$log</>";
    }

    private function printResult(string $desc, bool $result)
    {
        if ($result) {
            $this->info("✅ $desc");
        } else {
            $this->error("❌ $desc");
        }
    }

    private function successLog($log)
    {
        return "<fg=green>$log</>";
    }
}
