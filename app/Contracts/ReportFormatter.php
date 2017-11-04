<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 5/23/16
 * Time: 6:03 PM
 */

namespace App\Contracts;

interface ReportFormatter
{
    
    /*
     * This Interface will be used to format reports for the CPM Frontend
     */
    
    public function formatDataForNotesListingReport($notes, $request);

    public function formatDataForNotesAndOfflineActivitiesReport($patient);


    //public function formatDataForU20Report();

    //public function formatDataForAllBillingReport();

    public function formatDataForViewPrintCareplanReport($users);

    //public function formatDataForPatientActivitiesReport();

    //public function formatDataForPatientCarePlanPrintList();

    //public function formatDataForProgressReport();
}
