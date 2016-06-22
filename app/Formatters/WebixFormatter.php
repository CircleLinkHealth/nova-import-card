<?php namespace App\Formatters;

use App\ActivityMeta;
use App\Contracts\ReportFormatter;
use App\Models\CCD\CcdAllergy;
use App\Models\CCD\CcdMedication;
use App\Models\CCD\CcdProblem;
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

    public function formatDataForNotesListingReport($notes)
    {

        $count = 0;

        foreach($notes as $note){
            $patient = User::find($note->patient_id);

            if(!$patient){
                continue;
            }

            $formatted_notes[$count]['id'] = $note->id;

            //Display Name
            $formatted_notes[$count]['patient_name'] = $patient->display_name ? $patient->display_name : '';
            //ID
            $formatted_notes[$count]['patient_id'] = $note->patient_id;

            //Program Name
            $program = Program::find($patient->program_id);
            if ($program) $formatted_notes[$count]['program_name'] = $program->display_name;

            //Provider Name
            $provider = User::find(intval($patient->billingProviderID));
            if (is_object($provider)) {
                $formatted_notes[$count]['provider_name'] = $provider->fullName;
            } else {
                $formatted_notes[$count]['provider_name'] = '';
            }

            //Author
            $author = User::find($note->logger_id);
            if (is_object($author)) {
                $formatted_notes[$count]['author_name'] = $author->display_name;
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }

            //Type
            $formatted_notes[$count]['type'] = $note->type;

            //Status
            $formatted_notes[$count]['status'] = $note->type;

            //Comments
            $metaComment = ActivityMeta::where('activity_id',$note->id)
                ->where('meta_key', 'comment')->first();

            $meta = ActivityMeta::where('activity_id',$note->id)
                ->where(function($query){
                    $query->where('meta_key', 'call_status')
                        ->orWhere('meta_key', 'hospital')
                        ->OrWhere('meta_key', 'email_sent_to')
                        ->orWhere('meta_key', 'comment');
                })
                ->get();

            $formatted_notes[$count]['tags'] = '';
            $formatted_notes[$count]['comment'] = $metaComment->meta_value;
            $formatted_notes[$count]['date'] = Carbon::parse($note->created_at)->format('Y-m-d');

            //Check if note was sent to a provider
            $mail_forwarded_meta = ActivityMeta::where('activity_id',$note->id)
                ->where('meta_key', 'email_sent_to')
                ->get();

            foreach ($mail_forwarded_meta as $m) {
//                if ($m->meta_key == 'email_sent_to') {
//                    $sent_to_user = User::find($m->meta_value);
//                    if ($sent_to_user->providerInfo) {
                        $formatted_notes[$count]['tags'] = '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
//                    }
//                }
            }

            foreach ($meta as $m) {
                switch ($m->meta_value) {
                    case('reached'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                        break;
                    case('admitted'):
                        $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
                        break;
                }
            }


            //Topic / Offline Act Name
            //Preview
            //Date

            $count++;
        }

        return "data:" . json_encode(array_values($formatted_notes)) . "";
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

                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Decrease';

                    } else {

                        $biometric_values['verb'] = 'Increase';

                    }
                }

            }

            if ($metric == 'Weight') {

                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Decrease';

                    } else {

                        $biometric_values['verb'] = 'Increase';

                    }
                }

            }

            if($metric == 'Blood Sugar'){
                if($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    if ($biometric_values['starting'] > $biometric_values['target']) {

                        $biometric_values['verb'] = 'Decrease';

                    } else {

                        $biometric_values['verb'] = 'Increase';

                    }
                }
            }




            $careplanReport[$user->ID]['bio_data'][$metric]['target'] = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->ID]['bio_data'][$metric]['verb'] = $biometric_values['verb'];

        }//dd($careplanReport[$user->ID]['bio_data']);


    array_reverse($careplanReport[$user->ID]['bio_data']);

        //Medications List
        $careplanReport[$user->ID]['taking_meds'] = '';
        $meds = CcdMedication::where('patient_id', '=', $user->ID)->get();
        if($meds->count() > 0) {
            foreach($meds as $med) {
                $careplanReport[$user->ID]['taking_meds'] .= '<br>'.$med->name;
            }
        }

        //Allergies
        $careplanReport[$user->ID]['allergies'] = '';
        $allergies = CcdAllergy::where('patient_id', '=', $user->ID)->get();
        if($allergies->count() > 0) {
            foreach($allergies as $allergy) {
                $careplanReport[$user->ID]['allergies'] .= '<br>'.$allergy->allergen_name;
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