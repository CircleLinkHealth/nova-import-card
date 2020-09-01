<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoAttachServicesToNewLocation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $locationId;

    protected Carbon $month;

    protected LocationProcessorEloquentRepository $repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
        $this->month      = Carbon::now()->startOfMonth()->startOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //todo: file in progress, subject to change after dev or business side input
        $location = Location::with(['practice.locations' => function ($location) {
            $location->with(['chargeableServiceSummaries' => function ($summary) {
                $summary->with(['chargeableService'])
                    ->createdOn($this->month, 'chargeable_month');
            }])
                ->whereHas('chargeableServiceSummaries', function ($summary) {
                    $summary->createdOn($this->month, 'chargeable_month');
                });
        }])
            ->findOrFail($this->locationId);

        $practiceLocations = $location->practice->locations;

        if ($practiceLocations->isEmpty()) {
            $url = route('provider.dashboard.manage.chargeable-services', [
                'practiceSlug' => $location->practice->name,
            ]);
            $link = "<$url|Corresponding Practice's Chargeable Service management page>";
            //todo:decide channel
            sendSlackMessage('#channel-to-decide', "Auto attachment of Chargeable Services for new Location with ID:$this->locationId failed.
            Please head to $link and assign chargeable services to practice or location.");

            return;
        }

        $locationToCopy = $this->getLocationToCopyServicesFrom($practiceLocations);

        $locationToCopy->chargeableServiceSummaries->each(function (ChargeableLocationMonthlySummary $summary) {
            $this->repo()->store($this->locationId, $summary->chargeable_service_id, $this->month);
        });
        
        event(new LocationServicesAttached($this->locationId));
    }

    private function getLocationToCopyServicesFrom($practiceLocations): Location
    {
        $locationToCopy = $primaryLocation = $practiceLocations->where('is_primary', true)->first();

        if ( ! $primaryLocation) {
            $locationToCopy = $practiceLocations->first();
        }

        return $locationToCopy;
    }

    private function repo(): LocationProcessorEloquentRepository
    {
        if (is_null($this->repo)) {
            $this->repo = app(LocationProcessorEloquentRepository::class);
        }

        return $this->repo;
    }
}
