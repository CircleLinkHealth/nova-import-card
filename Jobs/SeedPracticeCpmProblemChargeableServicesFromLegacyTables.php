<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
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
    protected ?string $bhiCodeId;
    protected ?string $ccmCodeId;
    protected EloquentCollection $cpmProblems;
    protected ?string $pcmCodeId;

    protected int $practiceId;
    protected LocationProblemServiceRepository $repo;

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

        if (is_null($this->ccmCodeId) && is_null($this->bhiCodeId) && is_null($this->pcmCodeId)) {
            sendSlackMessage('#billing_alerts', 'Practice/Location Cpm Problem Chargeable Services seeding aborted - no Chargeable Services found.');

            return;
        }

        $this->setCpmProblems();

        $practice
            ->locations
            ->each(function (Location $location) use (&$toCreate, $practicePcmProblems) {
                LocationProblemService::where('location_id', $location->id)->delete();
                foreach ($this->cpmProblems as $problem) {
                    $isDementia = 'Dementia' === $problem->name;
                    $isDepression = 'Depression' === $problem->name;

                    $isBhi = $problem->is_behavioral;

                    $isPcm = $practicePcmProblems->filter(
                        function (PcmProblem $pcmProblem) use ($problem) {
                            return $pcmProblem->code === $problem->default_icd_10_code || $pcmProblem->description === $problem->name;
                        }
                    )->count() > 0;

                    if ($isBhi || $isDementia || $isDementia) {
                        $this->repo()->store($location->id, $problem->id, $this->bhiCodeId);
                    }

                    if ($isPcm) {
                        $this->repo()->store($location->id, $problem->id, $this->pcmCodeId);
                    }

                    if ( ! $isBhi || $isDementia || $isDepression) {
                        $this->repo()->store($location->id, $problem->id, $this->ccmCodeId);
                    }
                }
            });
    }

    private function repo(): LocationProblemServiceRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(LocationProblemServiceRepository::class);
        }

        return $this->repo;
    }

    private function setChargeableServices()
    {
        $chargeableServices = ChargeableService::get();
        if ($chargeableServices->isEmpty()) {
            return;
        }

        $this->pcmCodeId = optional($chargeableServices->firstWhere('code', ChargeableService::PCM))->id;
        $this->bhiCodeId = optional($chargeableServices->firstWhere('code', ChargeableService::BHI))->id;
        $this->ccmCodeId = optional($chargeableServices->firstWhere('code', ChargeableService::CCM))->id;
    }

    private function setCpmProblems()
    {
        $this->cpmProblems = CpmProblem::get();
    }
}
