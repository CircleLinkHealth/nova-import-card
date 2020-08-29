<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SeedPracticeCpmProblemChargeableServicesFromLegacyTables implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected string $bhiCode;
    protected string $ccmCode;
    protected EloquentCollection $cpmProblems;
    protected string $pcmCode;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Collection */
        $practice = Practice::with(['locations', 'pcmProblems'])
            ->findOrFail($this->practiceId);

        $practicePcmProblems = $practice->pcmProblems;
        $this->setChargeableServices();
        $this->setCpmProblems();

        $toCreate = collect([]);
        $practice
            ->locations
            ->each(function (Location $location) use (&$toCreate, $practicePcmProblems) {
                foreach ($this->cpmProblems as $problem) {
                    $isDementia = 'Dementia' === $problem->name;
                    $isDepression = 'Depression' === $problem->name;

                    $isBhi = $problem->is_behavioral;

                    $isPcm = $practicePcmProblems->where(
                        function ($q) use ($problem) {
                            $q->where('code', $problem->default_icd_10_code)
                                ->orWhere('description', $problem->name);
                        }
                    )->exists();
                    if ($isBhi || $isDementia || $isDementia) {
                        $toCreate->push([
                            'chargeable_service_id' => $this->bhiCode,
                            'location_id'           => $location->id,
                            'cpm_problem_id'        => $problem->id,
                        ]);
                    }

                    if ($isPcm) {
                        $toCreate->push([
                            'chargeable_service_id' => $this->pcmCode,
                            'location_id'           => $location->id,
                            'cpm_problem_id'        => $problem->id,
                        ]);
                    }

                    if ( ! $isBhi || $isDementia || $isDepression) {
                        $toCreate->push([
                            'chargeable_service_id' => $this->ccmCode,
                            'location_id'           => $location->id,
                            'cpm_problem_id'        => $problem->id,
                        ]);
                    }
                }
            });

        if ($toCreate->isEmpty()) {
            sendSlackMessage('#cpm_general_alerts', "Warning. Location CpmProblem services were not seeded for Practice: {$this->practiceId}");

            return;
        }

        LocationProblemService::insert(
            $toCreate->toArray()
        );
    }

    private function setChargeableServices()
    {
        $chargeableServices = ChargeableService::get();
        $this->pcmCode      = $chargeableServices->where('code', ChargeableService::PCM);
        $this->ccmCode      = $chargeableServices->where('code', ChargeableService::BHI);
        $this->bhiCode      = $chargeableServices->where('code', ChargeableService::CCM);
    }

    private function setCpmProblems()
    {
        $this->cpmProblems = CpmProblem::get();
    }
}
