<?php

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use App\Location;
use App\Services\ReportsService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateAprimaPdfCarePlan
{
    /**
     * Create the event listener.
     *
     * @param Location $location
     * @param ReportsService $reportsService
     */
    public function __construct(Location $location, ReportsService $reportsService)
    {
        $this->location = $location;
        $this->reportsService = $reportsService;
    }

    /**
     * Handle the event.
     *
     * @param  CarePlanWasApproved $event
     * @internal param Location $location
     * @internal param ReportsService $reportsService
     */
    public function handle(CarePlanWasApproved $event)
    {
        if (! auth()->user()->hasRole(['provider'])) return;

        $user = $event->patient;

        //Creating Reports for Aprima API
        //      Since there isn't a way to get the provider's location,
        //      we assume the patient's location and check it that
        //      is a child of Aprima's Location.
        $locationId = $user->getpreferredContactLocationAttribute();

        $locationObj = $this->location->find($locationId);

        if (!empty($locationObj) && $locationObj->parent_id == Location::APRIMA_ID) {
            $this->reportsService->createAprimaPatientCarePlanPdfReport($user, $user->getCarePlanProviderApproverAttribute());
        }
    }
}
