<?php namespace App\Services;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesQuestions;
use App\CPRulesUCP;
use App\Location;
use App\Observation;
use App\WpBlog;
use App\WpUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
Class ReportsService
{
    //Progress Report API
    public function progress($id)
    {
        $user = WpUser::find($id);
        //main container
        $progress = array();
        $userHeader = array();
        $trackingChanges = array();
        $medications = array();
        //USER HEADER:
        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user->display_name;
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = WpUser::find($userConfig['lead_contact']);
        $providerConfig = $provider->userConfig();
        $userHeader['Patient_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Provider_Name'] = $provider->display_name;
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($providerConfig['preferred_contact_location']);
        //**************TAKING YOUR MEDICATIONS SECTION**************
        $medications_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Medications to Monitor')->first();
        $medications_items = CPRulesItem::where('pcp_id', $medications_pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
        // gives the medications being monitered for the given user
        for($i = 0; $i < count($medications_items); $i++){
            //get id's of all medication items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $medications_items[$i])->where('meta_value', 'Active')->where('user_id',$user->ID)->first();
            if($item_for_user[$i] != null){
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $medications_categories[] = $user_items->items_text;
            }
        }//dd($medications_categories);
        //get all medication observations for the user
        $medication_obs = DB::connection('mysql_no_prefix')
            ->table('rules_questions')
            ->select('lv_observations.id','rules_items.items_text','lv_observations.obs_date','lv_observations.obs_value','lv_observations.obs_key','lv_observations.obs_message_id')
            ->join('lv_observations','rules_questions.msg_id','=','lv_observations.obs_message_id')
            ->join('rules_items','rules_questions.qid','=','rules_items.qid')
            ->where('user_id',$user->ID)
            ->where('lv_observations.obs_key','Adherence')
            ->where('lv_observations.obs_unit','!=','invalid')
            ->where('lv_observations.obs_unit','!=','scheduled')
            ->distinct('lv_observations.id')
            ->orderBy('lv_observations.id')
            ->get();
        //dd($medication_obs);
        //group observation readings by medicine
        $temp_meds = array();
        //Add scaffolding to sections
        //$medications['Data']['Good'] = array();
        $medications['Data']['Good']['description'] = 'Description for Good section';
        $medications['Data']['Needs Work']['description'] = 'Description for Needs Work section';
        $medications['Data']['Bad']['description'] = 'Description for Bad section';
        foreach ($medications_categories as $category){
            $yes = 0; $count = 0;
            foreach ($medication_obs as $obs){
                if($obs->items_text == $category) {
                    //$temp_meds[$category]['obs'][] = $obs; //(Will create arrays to categorize obs by medicine)
                    if(strtoupper($obs->obs_value) == 'Y') {
                        $yes++;
                    }
                    unset($medication_obs[$count]);
                    $count++;
                }
            }
            $temp_meds[$category]['yes'] = $yes;
            $temp_meds[$category]['total'] = $count;
            if($temp_meds[$category]['yes'] != 0 && $temp_meds[$category]['total'] != 0){
                //calculate ratio to tenth of a decimal place
                $temp_meds[$category]['percent'] = round($yes/$count,1);
            } else {$temp_meds[$category]['percent'] = 0;}
            //add to categories based on percenntage of responses
            switch($temp_meds[$category]['percent']) {
                case ($temp_meds[$category]['percent'] > 0.8):
                    $medications['Data']['Good'][] = ['name' => $category];
                    break;
                case ($temp_meds[$category]['percent'] >= 0.5):
                    $medications['Data']['Needs Work'][] = ['name' => $category];
                    break;
                case ($temp_meds[$category]['percent'] == 0):
                    $medications['Data']['Bad'][] = ['name' => $category];
                    break;
                default: $medications['Data']['Bad'][] = ['name' => $category];
                    break;
            }
            //echo $category.': ' . $temp_meds[$category]['percent'] . ' <br /> ';
        }
        //dd($temp_meds); //Show all the medication categories and stats
        //dd(json_encode($medications)); // show the medications by adherence category
        //**************TRACKING CHANGES SECTION**************
        //get observations for user to calculate adherence
        $obs_keys = array('Blood_Sugar','Blood_Pressure','Weight','Cigarettes');
        $tracking_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Biometrics to Monitor')->first();
        $tracking_items = CPRulesItem::where('pcp_id', $tracking_pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
        // gives the biometrics being monitered for the given user
        for($i = 0; $i < count($tracking_items); $i++){
            //get id's of all biometrics items that are active for the given user
            $items_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items[$i])->where('meta_value', 'Active')->where('user_id',$user->ID)->first();
            if($items_for_user[$i] != null){
                //Find the items_text for the ones that are active
                $user_items = CPRulesItem::find($items_for_user[$i]->items_id);
                $tracking_q = CPRulesQuestions::find($user_items->qid);
                //get all the message_ids active for the user
                $tracking_obs_keys[] = $tracking_q->msg_id;
            }
        }//dd($tracking_obs_keys);
        foreach($tracking_obs_keys as $q) {
            $tracking_obs[$q] = DB::table('observations')
                ->where('user_id', $user->ID)
                ->where('obs_message_id', $q)
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'scheduled')
                ->orderBy('obs_date','desc')
                ->limit(10)
                ->get();
            foreach($tracking_obs[$q] as $obs) {
                $tracking_obs[$q]['Data'][] = [
                    'Biometric' => $obs->obs_key, 'data' => $temp_meds];
            }
        }//dd($tracking_obs);
        // WRAPPING UP
        $progress['Progress_Report'][] = $userHeader;
        $trackingChanges['Section'] = 'Tracking Changes:';
        $progress['Progress_Report'][] = $trackingChanges;
        $medications['Section'] = 'Taking your medications?:';
        $progress['Progress_Report'][] = $medications;
        return json_encode($progress);
    }
    //CarePlan API
    public function careplan($id){
        //WE ARE TREATING
        $user = WpUser::find($id);
        $treating = array();
        $treating['Section'] = 'We Are Treating:';
        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->lists('pcp_id');
        //Get all the items for each section
        $tracking_items_ids = CPRulesItem::where('pcp_id', $pcp)->where('items_parent', 0)->lists('items_id');
        for($i = 0; $i < count($tracking_items_ids); $i++){
            //get id's of all items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items_ids[$i])->where('meta_value', 'Active')->where('user_id',$user->ID)->first();
            if($item_for_user[$i] != null){
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $treating['data'][] = ['name' => $user_items->items_text];
            }
        }//dd(json_encode($treating));
    }
}