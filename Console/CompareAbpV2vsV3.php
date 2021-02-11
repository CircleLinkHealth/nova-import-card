<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Http\Resources\PatientSuccessfulCallsCountForMonth;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompareAbpV2vsV3 extends Command
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
        'status',
        'approve',
        'reject',
        'report_id',
        'actor_id',
        'qa',
        'problems',
        'attested_ccm_problems',
        'attested_bhi_problems',
        'chargeable_services',
        'no_of_successful_calls',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare data integrity between ABP versions.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:abp-versions {practiceId}
                                                 {month?}
                                                 {--reversed : Whether the collections will be reversed (i.e. new vs old or old vs new}';

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
        $reversed   = (bool) $this->option('reversed');

        $aCollection = measureTime('v2', function () use ($practiceId, $month) {
            $service = app(ApproveBillablePatientsService::class);

            return $this->getData($service, $practiceId, $month);
        });

        $bCollection = measureTime('v3', function () use ($practiceId, $month) {
            $serviceV3 = app(ApproveBillablePatientsServiceV3::class);

            return $this->getData($serviceV3, $practiceId, $month);
        });

        if ($reversed) {
            $temp        = $aCollection;
            $aCollection = $bCollection;
            $bCollection = $temp;
        }

        $checkMore = $this->checkDataCount($aCollection, $bCollection);
        if ($checkMore) {
            $this->checkPatientIdsRetrieved($aCollection, $bCollection);
            $this->checkPatients($aCollection, $bCollection);
        }

        $this->newLine();
        $this->info('Done');

        return 0;
    }

    private function addSuccessfulCallCountsIfV3($service, Carbon $month, array $billablePatients): array
    {
        if (empty($billablePatients) || ! ($service instanceof ApproveBillablePatientsServiceV3)) {
            return $billablePatients;
        }

        $coll       = collect($billablePatients);
        $patientIds = $coll->map(fn ($item) => $item['id'])->toArray();
        $callCounts = $service->successfulCallsCount($patientIds, $month);

        return $coll
            ->map(function ($billablePatient) use ($callCounts) {
                /** @var PatientSuccessfulCallsCountForMonth $entry */
                $entry = $callCounts->collection->firstWhere('id', $billablePatient['id']);
                if ($entry) {
                    $billablePatient['no_of_successful_calls'] = $entry->toArray(null)['count'];
                }

                return $billablePatient;
            })
            ->toArray();
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

    private function checkChargeableServices(array $oldP, array $newP): string
    {
        $oldCs = collect($oldP['chargeable_services'])->map(fn ($item) => $item['id'])->toArray();
        $newCs = collect($newP['chargeable_services'])->map(fn ($item) => $item['id'])->toArray();

        if ($oldCs != $newCs) {
            $strA = implode(',', $oldCs);
            $strB = implode(',', $newCs);

            return $this->errorLog("$strA|$strB");
        }

        return $this->successLog('ok');
    }

    private function checkDataCount(?array $a, ?array $b): bool
    {
        $countOld    = $a ? sizeof($a) : 0;
        $countNew    = $b ? sizeof($b) : 0;
        $countEquals = $countOld === $countNew;
        $desc        = $countEquals ? "count[$countOld]" : "count[a:$countOld|b:$countNew]";
        $this->printResult($desc, $countEquals);

        return 0 !== $countOld && 0 !== $countNew;
    }

    private function checkPatient(array $aP, ?array $bP): array
    {
        $results = [$aP['id']];
        collect(self::PATIENT_PROPS_TO_CHECK)
            ->each(function ($prop) use ($aP, $bP, &$results) {
                $results[] = $this->checkPatientProperty($aP, $bP, $prop);
            });

        return $results;
    }

    private function checkPatientIdsRetrieved(array $a, array $b)
    {
        $allMatch = collect($a)->every(function ($aP, $index) use ($b) {
            $bP = $b[$index];

            return $aP['id'] === $bP['id'];
        });
        $this->printResult('matchingOrderOfPatientIds', $allMatch);
    }

    private function checkPatientProperty(array $aP, array $bP, string $prop): string
    {
        if (isset($aP[$prop]) && ! isset($bP[$prop])) {
            return $this->errorLog('b not set');
        }

        if ( ! isset($aP[$prop]) && isset($bP[$prop])) {
            return $this->errorLog('a not set');
        }

        if (is_array($aP[$prop]) && ! is_array($bP[$prop])) {
            return $this->errorLog('b not array');
        }

        if ( ! is_array($aP[$prop]) && is_array($bP[$prop])) {
            return $this->errorLog('a not array');
        }

        if ('chargeable_services' === $prop) {
            return $this->checkChargeableServices($aP, $bP);
        }

        if (is_array($aP[$prop])) {
            return $this->checkArrayProps($aP[$prop], $bP[$prop]) ? $this->successLog('ok') : $this->errorLog('x');
        }

        return $aP[$prop] === $bP[$prop] ? $this->successLog('ok') : $this->errorLog("a[$aP[$prop]]|b[$bP[$prop]]");
    }

    private function checkPatients(array $a, array $b)
    {
        $results = [];
        $bColl   = collect($b);
        collect($a)->each(function ($aP, $index) use ($bColl, &$results) {
            $bP = $bColl->get($index);
            if ( ! $bP || $bP['id'] !== $aP['id']) {
                $bP = $bColl->firstWhere(fn ($p) => $p['id'] === $aP['id']);
            }

            $results[] = $this->checkPatient($aP, $bP ?? []);
        });
        $this->table(['id', ...self::PATIENT_PROPS_TO_CHECK], $results);
    }

    private function errorLog($log)
    {
        return "<fg=red>$log</>";
    }

    private function getData($service, int $practiceId, Carbon $month)
    {
        $this->setRequest(0);

        $results    = $service->getBillablePatientsForMonth($practiceId, $month);
        $allResults = $this->addSuccessfulCallCountsIfV3($service, $month, $results->summaries->items());
        while ($results->summaries->nextPageUrl()) {
            $this->setRequest($results->summaries->currentPage() + 1);
            $results    = $service->getBillablePatientsForMonth($practiceId, $month);
            $arr        = $this->addSuccessfulCallCountsIfV3($service, $month, $results->summaries->items());
            $allResults = array_merge($allResults, $arr);
        }

        return json_decode(json_encode($allResults), true);
    }

    private function printResult(string $desc, bool $result)
    {
        if ($result) {
            $this->info("✅ $desc");
        } else {
            $this->error("❌ $desc");
        }
    }

    private function setRequest(int $page = 1)
    {
        /** @var Request $request */
        $request = app('request');
        if ( ! $request) {
            $request = new Request();
        }
        $request->replace(['page' => $page]);
        app()->bind('request', fn () => $request);
    }

    private function successLog($log)
    {
        return "<fg=green>$log</>";
    }
}
