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

    public function reportHeader($id){

        $user = WpUser::find($id);

        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user->display_name;
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = WpUser::find($userConfig['lead_contact']);
        $providerConfig = $provider->userConfig();
        $userHeader['Provider_Name'] = $provider->display_name;
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($userConfig['preferred_contact_location']);

        return $userHeader;
    }

    public function getItemsForParent($item, WpUser $user)
    {
        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', $item)->first();
        //Get all the items for each section
        $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
        for ($i = 0; $i < count($items); $i++) {
            //get id's of all lifestyle items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
            if ($item_for_user[$i] != null) {
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $categories[] = ['name' => $user_items->items_text];
            }
        }
        return $categories;
    }

    public function biometricsUnitMapping($biometric){

        switch($biometric){
            case 'Blood Sugar': return ' mg/dL'; break;
            case 'Cigarettes': return ''; break;
            case 'Weight': return ' lbs'; break;
            case 'Blood Pressure': return ' mm Hg'; break;
            default: return '';
        }
    }

    public function progress($id)
    {
        $user = WpUser::find($id);

        //main container

        $progress = array();
        $userHeader = $this->reportHeader($id);
        $trackingChanges = array();
        $medications = array();

        $medications['Section'] = 'Taking your medications?:';
        $trackingChanges['Section'] = 'Tracking Changes:';

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
            ->where('obs_unit', '!=', 'outbound')
            ->distinct('lv_observations.id')
            ->orderBy('lv_observations.id')
            ->get();

        //dd($medication_obs);

        //group observation readings by medicine

        $temp_meds = array();

        //Add scaffolding to sections

        $meds_array['Good']['description'] = '';
        $meds_array['Needs Work']['description'] = '';
        $meds_array['Bad']['description'] = '';

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

            //add to categories based on percentage of responses
            switch($temp_meds[$category]['percent']) {
                case ($temp_meds[$category]['percent'] > 0.8):
                    $meds_array['Good']['description'] .= ($meds_array['Good']['description'] == '' ? $category : ', ' . $category);
                    break;
                case ($temp_meds[$category]['percent'] >= 0.5):
                    $meds_array['Needs Work']['description'] .= ($meds_array['Needs Work']['description'] == '' ? $category : ', ' . $category);
                    break;
                case ($temp_meds[$category]['percent'] == 0):
                    $meds_array['Bad']['description'] .= ($meds_array['Bad']['description'] == '' ? $category : ', ' . $category);
                    break;
                default: $meds_array['Bad']['description'] .= ($meds_array['Bad']['description'] == '' ? $category : ', ' . $category);
                    break;
            }
            //echo $category.': ' . $temp_meds[$category]['percent'] . ' <br /> ';
        }
        //dd($temp_meds); //Show all the medication categories and stats
        //dd(json_encode($medications)); // show the medications by adherence category

        $medications['Data'][] = $meds_array['Good'];
        $medications['Data'][] = $meds_array['Needs Work'];
        $medications['Data'][] = $meds_array['Bad'];


        //**************TRACKING CHANGES SECTION**************

        //get observations for user to calculate adherence
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
                $tracking_obs_message_ids[] = $tracking_q->msg_id;

                //map obs_message_id => obs_key ("CF_RPT_50" => "Cigarettes")
                $tracking_obs_question_map[$tracking_q->msg_id] = str_replace('_',' ',$tracking_q->obs_key);

                //get all the targets for biometrics that are being observed
                $target_items = CPRulesItem::where('items_parent', $items_for_user[$i]->items_id)->where('items_text', 'like', '%Target%')->get();
                    foreach ($target_items as $target_item){
                        $target_value = CPRulesUCP::where('items_id', $target_item->items_id)->where('user_id', $user->ID)->lists('meta_value');
                        $target_array[str_replace('_',' ',$tracking_q->obs_key)] = $target_value[0];
                    }
            }
        }//dd($tracking_obs_question_map);

        foreach($tracking_obs_message_ids as $q) {

            // average for last 10 observations
            if ($q == 'CF_RPT_20') {
                $tracking_obs_data[$q] = DB::table('observations')
                    ->select(DB::raw('date_format(max(obs_date), \'%c/%e\') as week, floor(avg(CAST(SUBSTRING_INDEX(obs_value, \'/\', 1) as UNSIGNED))) as Reading'))
                    ->where('user_id', $user->ID)
                    ->where('obs_message_id', $q)
                    ->where('obs_unit', '!=', 'invalid')
                    ->where('obs_unit', '!=', 'scheduled')
                    ->where('obs_unit', '!=', 'outbound')
                    ->limit(10)
                    ->orderBy('obs_date', 'desc')
                    ->groupBy(DB::raw('WEEK(obs_date)'))
                    ->get();

            } else {

            $tracking_obs_data[$q] = DB::table('observations')
                ->select(DB::raw('date_format(max(obs_date), \'%c/%e\') as week, floor(avg(obs_value)) as Reading'))
                ->where('user_id', $user->ID)
                ->where('obs_message_id', $q)
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'scheduled')
                ->where('obs_unit', '!=', 'outbound')
                //->where(DB::raw('obs_date >= CURDATE() - INTERVAL 10 WEEK'))
                ->limit(10)
                ->orderBy('obs_date', 'desc')
                ->groupBy(DB::raw('WEEK(obs_date)'))
                ->get();
            }

            //Week Over Week Comparison

            $num_obs = count($tracking_obs_data[$q]);

            for ($i = 0; $i < count($tracking_obs_data[$q]); $i++) {
                $tracking_obs_data[$q][$i]->id = $i+1;
                    }

            if(!$tracking_obs_data[$q]){return 'Error';}
            //store all the modified data in this array

            if($num_obs > 2) {
                $change = abs($tracking_obs_data[$q][0]->Reading - $tracking_obs_data[$q][$num_obs-1]->Reading);
                if ($tracking_obs_question_map[$q] == 'Cigarettes') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]->Reading > $tracking_obs_data[$q][1]->Reading) {
                        $status = 'Bad';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]->Reading == $tracking_obs_data[$q][1]->Reading) {
                        $status = 'No Change';
                        $progression = 'N/A';
                    } else {
                        $status = 'Good';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Blood Pressure') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]->Reading > $tracking_obs_data[$q][1]->Reading) {
                        $status = 'Bad';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]->Reading == $tracking_obs_data[$q][1]->Reading) {
                        $status = 'No Change';
                        $progression = 'N/A';
                    } else {
                        $status = 'Good';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Blood Sugar') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]->Reading > $tracking_obs_data[$q][1]->Reading) {
                        $status = 'Bad';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]->Reading == $tracking_obs_data[$q][1]->Reading) {
                        $status = 'No Change';
                        $progression = 'N/A';
                    } else {
                        $status = 'Good';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Weight') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]->Reading > $tracking_obs_data[$q][1]->Reading) {
                        $status = 'Bad';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]->Reading == $tracking_obs_data[$q][1]->Reading) {
                        $status = 'No Change';
                        $progression = 'N/A';
                    } else {
                        $status = 'Good';
                        $progression = 'down';
                    }
                }
            }
            else {
                $status = 'Unchanged';
                $progression = 'Unchanged';
                $unit = '';
                $change = 'Unchanged';
            }
            $trackingChanges['Data'][] =
                [
                    'Biometric' => $tracking_obs_question_map[$q],
                   //'Latest Weekly Avg.' => $tracking_obs_data[$q][0]->avg,
                    'Status' => $status,
                    'Progression' => $progression,
                    'Change: ' => $change . $unit,
                    'Latest Weekly Data: ' => $tracking_obs_data[$q][0]->Reading . $unit,
                    'Goal' => $target_array[$tracking_obs_question_map[$q]],
                    'data' => $tracking_obs_data[$q]
                ];
            //, 'data' => $temp_meds];

        }
        //dd($trackingChanges['Data']);
        //dd($tracking_obs_data);

        // WRAPPING UP
        $progress['Progress_Report'][] = $userHeader;
        $progress['Progress_Report'][] = $trackingChanges;
        $progress['Progress_Report'][] = $medications;
        return $progress;
    }
    //CarePlan API

    public function careplan($id){

        $user = WpUser::find($id);
        $careplan['CarePlan_Report'][] = $this->reportHeader($id);

        //=======================================
        //========WE ARE TREATING================
        //=======================================

        $treating['Section'] = 'We Are Treating:';

            //PCP has the sections for each provider, get all sections for the user's blog
            $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->first();
            //Get all the items for each section
            $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id');


            for ($i = 0; $i < count($items); $i++) {
                //get id's of all lifestyle items that are active for the given user
                $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
                $items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
                $items_detail_ucp[$i] = CPRulesUCP::where('items_id',$items_detail[$i]->items_id)->where('user_id', $user->ID)->first();
                if ($item_for_user[$i] != null) {
                    $count = 0;
                    //Find the items_text for the one's that are active
                    $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                    $treating['Data'][] = ['name' =>$user_items->items_text,'comment' => ($items_detail_ucp[$i]->meta_value == '' ? 'Nothing' : $items_detail_ucp[$i]->meta_value) ];

                }
            }

        //=======================================
        //========YOUR HEALTH GOALS==============
        //=======================================

        $goals['Section'] = 'Your Health Goals';

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
                $tracking_obs_message_ids[] = $tracking_q->msg_id;

                //map obs_message_id => obs_key ("CF_RPT_50" => "Cigarettes")
                $tracking_obs_question_map[$tracking_q->msg_id] = str_replace('_',' ',$tracking_q->obs_key);

                // @todo setup starting reading from obsmeta

                //get all the targets for biometrics that are being observed
                $target_items = CPRulesItem::where('items_parent', $items_for_user[$i]->items_id)->where('items_text', 'like', '%Target%')->get();
                foreach ($target_items as $target_item){
                    $target_value = CPRulesUCP::where('items_id', $target_item->items_id)->where('user_id', $user->ID)->lists('meta_value');
                    $target_array[str_replace('_',' ',$tracking_q->obs_key)] = $target_value[0];
                }
            }
        }//dd($target_array);

        foreach($target_array as $key => $value){
            $goals['Data'][] = ['name' => '<B>Lower '. $key .' to '.$value . $this->biometricsUnitMapping($key).' </B> from  [STARTING READING]'.$this->biometricsUnitMapping($key)];
        }

        //=======================================
        //==============MEDICATIONS==============
        //=======================================

            //Monitoring Medications

        $medications['Section'] = 'Medications';
        $medications['Data']['Monitoring These Medications'] = $this->getItemsForParent('Medications to Monitor', $user);

            //Taking Medications

        $additional_information_item = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Additional Information')->first();
        $medication_information_item = CPRulesItem::where('pcp_id', $additional_information_item->pcp_id)->where('items_parent', 0)->where('items_text', 'Medications List')->first();
        $medication_tracking_item = CPRulesItem::where('items_parent', $medication_information_item->items_id)->first();
        $medications_taking = CPRulesUCP::where('items_id', $medication_tracking_item->items_id)->where('user_id',$user->ID)->first();

        $medications['Data']['Taking These Medications'][] = $medications_taking->meta_value;

        //=======================================
        //========SYMPTOMS TO MONITOR============
        //=======================================

        $symptoms['Section'] = 'Watch out for';
        $symptoms['Data'] = $this->getItemsForParent('Symptoms to Monitor', $user);


        //=======================================
        //========LIFESTYLE TO MONITOR===========
        //=======================================

        $lifestyle['Section'] = 'We Are Informing You About';
        $lifestyle['Data'] = $this->getItemsForParent('Lifestyle to Monitor', $user);

        //ADD ALL TO MAIN ARRAY
        $careplan['CarePlan_Report'][] = $treating;
        $careplan['CarePlan_Report'][] = $goals;
        $careplan['CarePlan_Report'][] = $medications;
        $careplan['CarePlan_Report'][] = $symptoms;

        return $careplan;

    }
}