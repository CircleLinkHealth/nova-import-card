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
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CarePlanWasApproved  $event
     * @return void
     */
    public function handle(CarePlanWasApproved $event, Location $location, ReportsService $reportsService)
    {
        if (! auth()->user()->hasRole(['provider'])) return;

        $user = $event->patient;

        //Creating Reports for Aprima API
        //      Since there isn't a way to get the provider's location,
        //      we assume the patient's location and check it that
        //      is a child of Aprima's Location.
        $locationId = $user->getpreferredContactLocationAttribute();

        $locationObj = $location->find($locationId);

        if (!empty($locationObj) && $locationObj->parent_id == Location::APRIMA_ID) {
            $reportsService->createAprimaPatientCarePlanPdfReport($user, $user->getCarePlanProviderApproverAttribute());
        }
    }
}
