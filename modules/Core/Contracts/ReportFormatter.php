<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ReportFormatter
{
    public function formatDataForNotesAndOfflineActivitiesReport($patient);

    // This Interface will be used to format reports for the CPM Frontend

    public function formatDataForNotesListingReport($notes, $request);

    //public function formatDataForU20Report();

    //public function formatDataForAllBillingReport();

    public function formatDataForViewPrintCareplanReport($users);

    public function patientListing(Collection $patients = null);

    //public function formatDataForPatientActivitiesReport();

    //public function formatDataForPatientCarePlanPrintList();

    //public function formatDataForProgressReport();
}
