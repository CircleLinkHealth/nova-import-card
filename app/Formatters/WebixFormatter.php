<?php namespace App\Formatters;

use App\Contracts\ReportFormatter;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmMisc;
use App\Services\CPM\CpmMiscService;
use App\Services\NoteService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;

class WebixFormatter implements ReportFormatter
{

    //Transform Reports Data for Webix

    public function formatDataForNotesListingReport($notes, $request)
    {
        $count = 0;

        $formatted_notes = [];

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

            if (count($note->mail) > 0) {
                if((new NoteService())->wasSentToProvider($note)){
                    $formatted_notes[$count]['tags'] .= '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
                }
            }


            if (count($note->call) > 0) {
                if ($note->call->status == 'reached') {
                    $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                }
            }

            if ($note->isTCM) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $count++;
        }

        return $formatted_notes;

    }

    public function formatDataForNotesAndOfflineActivitiesReport($report_data)
    {

        if ($report_data->isEmpty()) {
            return '';
        }

        $formatted_data = [];
        $count = 0;

        foreach ($report_data as $data) {

            $formatted_data[$count]['id'] = $data->id;


            if (isset($data->author_id)) // only notes have authors
            {
                $formatted_data[$count]['logger_name'] = User::withTrashed()->find($data->author_id)->fullName;
                $formatted_data[$count]['comment'] = $data->body;
                $formatted_data[$count]['logged_from'] = 'note';

            } else // handles activities
            {
                $formatted_data[$count]['logger_name'] = User::withTrashed()->find($data->provider_id)->fullName;
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

        $careplanReport = [];

        foreach ($users as $user) {

//            if (!is_object($user)) {
//                $user = User::find($user);
//            }

            $careplanReport[$user->ID]['symptoms'] = $user->cpmSymptoms()->get()->pluck('name')->all();
            $careplanReport[$user->ID]['problem'] = $user->cpmProblems()->get()->pluck('name')->all();
            $careplanReport[$user->ID]['problems'] = (new \App\Services\CPM\CpmProblemService())->getProblemsWithInstructionsForUser($user);
            $careplanReport[$user->ID]['lifestyle'] = $user->cpmLifestyles()->get()->pluck('name')->all();
            $careplanReport[$user->ID]['biometrics'] = $user->cpmBiometrics()->get()->pluck('name')->all();
            $careplanReport[$user->ID]['medications'] = $user->cpmMedicationGroups()->get()->pluck('name')->all();
        }

        $other_problems = (new ReportsService())->getInstructionsforOtherProblems($user);

        if(!empty($other_problems)) {
            $careplanReport[$user->ID]['problems']['Other Problems'] = $other_problems;
        }

        //Get Biometrics with Values
        $careplanReport[$user->ID]['bio_data'] = [];

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

                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    $starting = explode('/', $biometric_values['starting']);
                    $starting = $starting[0];
                    $target = explode('/', $biometric_values['target']);
                    $target = $target[0];

                    if ($starting > $target) {

                        $biometric_values['verb'] = 'Decrease';

                    } else if ($starting < $target){

                        $biometric_values['verb'] = 'Increase';

                    } else {

                        $biometric_values['verb'] = 'Regulate';

                    }

                    if($starting >= 100 && $starting <= 130) {
                        $biometric_values['verb'] = 'Regulate';
                    }
                }

            }

            if ($metric == 'Weight') {

                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Decrease';

                    } else if ($biometric_values['starting'] < $biometric_values['target']){

                        $biometric_values['verb'] = 'Increase';

                    } else {

                        $biometric_values['verb'] = 'Regulate';

                    }
                }

            }

            if($metric == 'Blood Sugar'){
                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Decrease';

                    } else if ($biometric_values['starting'] < $biometric_values['target']){

                        $biometric_values['verb'] = 'Increase';

                    } else {

                        $biometric_values['verb'] = 'Regulate';

                    }
                }

                if(intval($biometric_values['starting']) >= 70 && intval($biometric_values['starting']) <= 130) {
                    $biometric_values['verb'] = 'Regulate';
                }


            }




            $careplanReport[$user->ID]['bio_data'][$metric]['target'] = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['verb'] = $biometric_values['verb'];

        }//dd($careplanReport[$user->ID]['bio_data']);


        array_reverse($careplanReport[$user->ID]['bio_data']);

        //Medications List
        $careplanReport[$user->ID]['taking_meds'] = 'No instructions at this time';
        $medicationList = $user->cpmMiscs->where('name',CpmMisc::MEDICATION_LIST)->all();
        if(!empty($medicationList)) {
            $meds = CcdMedication::where('patient_id', '=', $user->ID)->orderBy('name')->get();
            if ($meds->count() > 0) {
                $i = 0;
                $careplanReport[$user->ID]['taking_meds'] = [];
                foreach ($meds as $med) {
                    empty($med->name) 
                        ? $medText = ''
                        : $medText = ''.$med->name;

                    if(!empty($med->sig)) {
                        $medText .= '<br /><span style="font-style:italic;">- '.$med->sig.'</span>';
                    }
                    $careplanReport[$user->ID]['taking_meds'][] = $medText;
                    $i++;
                }
            }
        }

        //Allergies
        $careplanReport[$user->ID]['allergies'] = 'No instructions at this time';
        $allergy = $user->cpmMiscs->where('name',CpmMisc::ALLERGIES)->all();
        if(!empty($allergy)){
            $allergies = CcdAllergy::where('patient_id', '=', $user->ID)->orderBy('allergen_name')->get();
            if($allergies->count() > 0) {
                $careplanReport[$user->ID]['allergies'] = '';
                $i = 0;
                foreach($allergies as $allergy) {
                    if(empty($allergy->allergen_name)) {
                        continue 1;
                    }
                    if($i > 0) {
                        $careplanReport[$user->ID]['allergies'] .= '<br>';
                    }
                    $careplanReport[$user->ID]['allergies'] .= $allergy->allergen_name;
                    $i++;
                }
            }
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