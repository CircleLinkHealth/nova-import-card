<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Call;
use App\Contracts\ReportFormatter;
use App\MailLog;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmMisc;
use App\Program;
use App\Services\CPM\CpmMiscService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;

class WebixFormatter implements ReportFormatter
{

    //Transform Reports Data for Webix

    public function formatDataForNotesListingReport($notes, $request)
    {
        $count = 0;

        $formatted_notes = array();

        foreach ($notes as $note) {

            $formatted_notes[$count]['id'] = $note->id;

            //Display Name
            $formatted_notes[$count]['patient_name'] = $note->patient->display_name ? $note->patient->display_name : '';
            //ID
            $formatted_notes[$count]['patient_id'] = $note->patient_id;

            $formatted_notes[$count]['program_name'] = $note->patient->primaryProgram->display_name;

            //Provider Name
            $provider = User::find(intval($note->patient->billingProviderID));
            if (is_object($provider)) {
                $formatted_notes[$count]['provider_name'] = $provider->fullName;
            } else {
                $formatted_notes[$count]['provider_name'] = '';
            }

            //Author
            $author = $note->author;
            if (is_object($author)) {
                $formatted_notes[$count]['author_name'] = $author->display_name;
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }

            //Type
            $formatted_notes[$count]['type'] = $note->type;

            //Body
            $formatted_notes[$count]['comment'] = $note->body;

            $formatted_notes[$count]['date'] = Carbon::parse($note->performed_at)->format('Y-m-d');

            //TAGS
            $formatted_notes[$count]['tags'] = '';

            if (($note->mail != null)) {
                $formatted_notes[$count]['tags'] = '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
            }


            if (($note->call != null)) {
                if ($note->call->status == 'reached') {
                    $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                }
            }

            if ($note->isTCM == true) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $count++;
        }

//        //This would contain all data to be sent to the view
//        $notes = array();
//
//        //Get current page form url e.g. &page=6
//        $currentPage = LengthAwarePaginator::resolveCurrentPage();
//
//        //Create a new Laravel collection from the array data
//        $collection = new Collection($formatted_notes);
//
//        //Define how many items we want to be visible in each page
//        $per_page = 15;
//
//        //Slice the collection to get the items to display in current page
//        $currentPageResults = $collection->slice(($currentPage-1) * $per_page, $per_page)->all();
//
//        //Create our paginator and add it to the data array
//        $results = new LengthAwarePaginator($currentPageResults, count($collection), $per_page);
//
//        //Set base url for pagination links to follow e.g custom/url?page=6
//        $results->setPath($request->url());

        return $formatted_notes;

    }

    public function formatDataForNotesAndOfflineActivitiesReport($report_data)
    {

        if ($report_data->isEmpty()) {
            return '';
        }

        $formatted_data = array();
        $count = 0;

        foreach ($report_data as $data) {

            $formatted_data[$count]['id'] = $data->id;


            if (is_int($data->author_id)) // only notes have authors
            {
                $formatted_data[$count]['logger_name'] = User::find($data->author_id)->fullName;
                $formatted_data[$count]['comment'] = $data->body;
                $formatted_data[$count]['logged_from'] = 'note';

            } else // handles activities
            {
                $formatted_data[$count]['logger_name'] = User::find($data->logger_id)->fullName;
                $formatted_data[$count]['comment'] = $data->getCommentForActivity();
                $formatted_data[$count]['logged_from'] = 'manual_input';
            }

            $formatted_data[$count]['type_name'] = $data->type;
            $formatted_data[$count]['performed_at'] = $data->performed_at;


            $count++;

        }

        if (!empty($formatted_data)) {

            return "data:" . json_encode($formatted_data) . "";

        } else {

            return '';

        }
    }

    public function formatDataForViewPrintCareplanReport($users)
    {

        $careplanReport = array();

        foreach ($users as $user) {

//            if (!is_object($user)) {
//                $user = User::find($user);
//            }

            $careplanReport[$user->ID]['symptoms'] = $user->cpmSymptoms()->get()->lists('name')->all();
            $careplanReport[$user->ID]['problem'] = $user->cpmProblems()->get()->lists('name')->all();
            $careplanReport[$user->ID]['problems'] = (new \App\Services\CPM\CpmProblemService())->getProblemsWithInstructionsForUser($user);
            $careplanReport[$user->ID]['lifestyle'] = $user->cpmLifestyles()->get()->lists('name')->all();
            $careplanReport[$user->ID]['biometrics'] = $user->cpmBiometrics()->get()->lists('name')->all();
            $careplanReport[$user->ID]['medications'] = $user->cpmMedicationGroups()->get()->lists('name')->all();
        }

        $other_problems = (new ReportsService())->getInstructionsforOtherProblems($user);

        if(!empty($other_problems)) {
            $careplanReport[$user->ID]['problems']['Other Problems'] = $other_problems;
        }

        //Get Biometrics with Values
        $careplanReport[$user->ID]['bio_data'] = array();

        //Ignore Smoking - Untracked Biometric
        if(($key = array_search(CpmBiometric::SMOKING, $careplanReport[$user->ID]['biometrics'])) !== false) {
            unset($careplanReport[$user->ID]['biometrics'][$key]);
        }

        foreach ($careplanReport[$user->ID]['biometrics'] as $metric) {

            $biometric = $user->cpmBiometrics->where('name', $metric)->first();
            $biometric_values = app(config('cpmmodelsmap.biometrics')[$biometric->type])->getUserValues($user);

            if($biometric_values){
                //Check to see whether the user has a starting value
                if ($biometric_values['starting'] == '') {
                    $biometric_values['starting'] = 'N/A';
                }

                //Check to see whether the user has a target value
                if ($biometric_values['target'] == '') {
                    $biometric_values['target'] = 'TBD';
                }

                //If no values are retrievable, then default to these:
            } else {
                $biometric_values['starting'] = 'N/A';
                $biometric_values['target'] = 'TBD';
            }

            //Special verb use for each biometric
            if($metric == 'Blood Pressure'){

                if($biometric_values['starting'] == 'N/A') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    $biometric_values['verb'] = 'Maintain';
                }

            }

            if ($metric == 'Weight') {

                $biometric_values['verb'] = 'Maintain';

            }

            if($metric == 'Blood Sugar'){
                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Lower';

                    } else {

                        $biometric_values['verb'] = 'Raise';

                    }
                }
            }




            $careplanReport[$user->ID]['bio_data'][$metric]['target'] = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['verb'] = $biometric_values['verb'];

        }//dd($careplanReport[$user->ID]['bio_data']);


    array_reverse($careplanReport[$user->ID]['bio_data']);

        //Medications List
        if($user->cpmMiscs->where('name',CpmMisc::MEDICATION_LIST)->first()){
            $careplanReport[$user->ID]['taking_meds'] = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::MEDICATION_LIST);
        } else {
            $careplanReport[$user->ID]['taking_meds'] = '';
        }

        //Allergies
        if($user->cpmMiscs->where('name',CpmMisc::MEDICATION_LIST)->first()){
            $careplanReport[$user->ID]['allergies'] = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::ALLERGIES);
        } else {
            $careplanReport[$user->ID]['allergies'] = '';
        }

        //Social Services
        if($user->cpmMiscs->where('name',CpmMisc::SOCIAL_SERVICES)->first()){
            $careplanReport[$user->ID]['social'] = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::SOCIAL_SERVICES);
        } else {
            $careplanReport[$user->ID]['social'] = '';
        }

        //Other
        if($user->cpmMiscs->where('name',CpmMisc::OTHER)->first()){
            $careplanReport[$user->ID]['other'] = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::OTHER);
        } else {
            $careplanReport[$user->ID]['other'] = '';
        }

        //Appointments
        if($user->cpmMiscs->where('name',CpmMisc::APPOINTMENTS)->first()){
            $careplanReport[$user->ID]['appointments'] = (new CpmMiscService())->getMiscWithInstructionsForUser($user,CpmMisc::APPOINTMENTS);
        } else {
            $careplanReport[$user->ID]['appointments'] = '';
        }

//        array_reverse($biometrics)
        return $careplanReport;

    }


}