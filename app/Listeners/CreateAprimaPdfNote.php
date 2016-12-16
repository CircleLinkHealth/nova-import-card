<?php

namespace App\Listeners;

use App\Events\NoteWasForwarded;
use App\Location;
use App\Services\ReportsService;

class CreateAprimaPdfNote
{
    protected $location;
    protected $reportsService;

    /**
     * Create the event listener.
     *
     * @param Location $location
     * @param ReportsService $reportsService
     */
    public function __construct(
        Location $location,
        ReportsService $reportsService
    ) {
        $this->location = $location;
        $this->reportsService = $reportsService;
    }

    /**
     * Handle the event.
     *
     * @param  NoteWasForwarded $event
     *
     * @return void
     */
    public function handle(NoteWasForwarded $event)
    {
        if ($event->patient->program_id != 16) {
            return;
        }

        //Creating Reports for Aprima API
        //      Since there isn't a way to get the provider's location,
        //      we assume the patient's location and check it that
        //      is a child of Aprima's Location.
        $locationId = $event->patient->getpreferredContactLocationAttribute();

        $locationObj = $this->location->find($locationId);

        if (!empty($locationObj) && $locationObj->practice->name == 'upg') {
            $this->reportsService->createNotePdfReport($event->patient, $event->sender, $event->note, $event->careteam);
        }
    }
}
