<?php namespace App\Services;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesQuestions;
use App\CPRulesUCP;
use App\Location;
use App\Observation;
use App\WpBlog;
use App\WpUser;
use App\WpUserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

Class ReportsService
{
    //Progress Report API

    public function reportHeader($id)
    {

        $user = WpUser::find($id);
        $user_meta = WpUserMeta::where('user_id', '=', $user->ID)->lists('meta_value', 'meta_key');
        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user_meta['first_name'] . ' ' . $user_meta['last_name'];
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = WpUser::findOrFail($userConfig['billing_provider']);
        $providerConfig = $provider->userConfig();
        $provider_meta = WpUserMeta::where('user_id', '=', $provider->ID)->lists('meta_value', 'meta_key');
        $userHeader['Provider_Name'] = trim($providerConfig['prefix'] . ' ' . $provider_meta['first_name'] . ' ' . $provider_meta['last_name'] . ' ' . $providerConfig['qualification']);
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($userConfig['preferred_contact_location']);

        return $userHeader;
    }

    public static function getItemsForParent($item, WpUser $user)
    {
        $categories = array();
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
                $categories[] = [
                    'name' => $user_items->items_text,
                    'items_id' => $user_items->items_id,
                    'section_text' => $item,
                    'items_text' => $user_items->items_text
                ];
            }
        }
        if (count($categories) > 0) {
            return $categories;
        } else {
            return false;
        }
    }

    public function biometricsUnitMapping($biometric)
    {

        switch ($biometric) {
            case 'Blood Sugar':
                return ' mg/dL';
                break;
            case 'Cigarettes':
                return '';
                break;
            case 'Weight':
                return ' lbs';
                break;
            case 'Blood Pressure':
                return ' mm Hg';
                break;
            default:
                return '';
        }
    }

    public function biometricsMessageIdMapping($biometric)
    {
        switch ($biometric) {
            case 'Blood Sugar':
                return 'CF_RPT_30';
                break;
            case 'Cigarettes':
                return 'CF_RPT_50';
                break;
            case 'Weight':
                return 'CF_RPT_40';
                break;
            case 'Blood Pressure':
                return 'CF_RPT_20';
                break;
            default:
                return '';
        }
    }

    /**
     * @param $weeklyReadingFirst
     * @param $weeklyReadingLast
     * @param $biometric
     *
     * Returns an Array with Color and Arrow for a given Biometric,
     * and a pair of reading, Usually First Week and Last Week
     */
    public function biometricsIndicators($weeklyReadingFirst, $weeklyReadingLast, $biometric, $target){

        $change = $weeklyReadingFirst - $weeklyReadingLast;
        $changes_array = array();
        // arrow [progression]
        if($weeklyReadingFirst < $weeklyReadingLast) {
            $changes_array['progression'] = 'up';
        } else if($weeklyReadingFirst > $weeklyReadingLast) {
            $changes_array['progression'] = 'down';
        }

        // obs_key specific labelling
        $target = explode('/', $target);
        if( ( $biometric == 'Blood_Pressure' || $biometric == 'Blood_Sugar') ) {
            // within range, green good
            if ($biometric == 'Blood_Pressure') {
                if ($weeklyReadingLast > 100 && $weeklyReadingLast < 141) {
                    $changes_array['color'] = 'green';
                }
            }
            if ($biometric == 'Blood_Sugar') {
                // within range, green good
                if ($weeklyReadingLast > 80 && $weeklyReadingLast < 141) {
                    $changes_array['color'] = 'green';
                }
            }
            // unchanged?
            if($weeklyReadingLast == $weeklyReadingFirst){
                $changes_array['color'] = 'yellow';
                $changes_array['progression'] = 'Unchanged'; // no arrow
            }
            // outside of range
            if (!isset($changes_array['color'])) {
                // if current reading is ABOVE target reading
                if ($weeklyReadingLast > $target) {
                    if ($change < 0) { // over goal and dropping
                        $changes_array['color'] = 'green';
                    } else if ($change > 0) { // over goal and rising
                        $changes_array['color'] = 'red';
                    }
                }
                // if current reading is BELOW target reading
                if ($weeklyReadingLast < $target) {
                    if ($change > 0) { // under goal and rising
                        $changes_array['color'] = 'better';
                    } else if ($change < 0) { // under goal and dropping
                        $changes_array['color'] = 'red';
                    }
                }
            }
        }
        if($biometric == 'Weight' || 'Cigarettes') {
            // within range, green good
            if ($weeklyReadingLast <= $target) {
                $changes_array['color'] = 'green';
            }
            // unchanged?
            if($weeklyReadingLast == $weeklyReadingFirst) {
                $changes_array['color'] = 'unchanged';
                $changes_array['progression'] = 'Unchanged';
            }
            // outside of range
            if (!isset($changes_array['color'])) {
                if ($weeklyReadingLast > $target && $change < 0) { // over goal and dropping
                    $changes_array['color'] = 'green';
                } else if ($weeklyReadingLast > $target && $change > 0) { // over goal and rising
                    $changes_array['color'] = 'red';
                }
            }
        }

        return $changes_array;
    }

    public function progress($id)
    {
        $user = WpUser::find($id);

        //main container

        $progress = array();
        $userHeader = $this->reportHeader($id);
        $trackingChanges = array();
        $medications = array();
        $colors = array('green', 'red', 'yellow');

        $medications['Section'] = 'Taking your <b>Medications</b>?';
        $trackingChanges['Section'] = 'Tracking Changes';

        //**************TAKING YOUR MEDICATIONS SECTION**************

        $medications_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Medications to Monitor')->first();
        $medications_items = CPRulesItem::where('pcp_id', $medications_pcp->pcp_id)->where('items_parent', 0)->lists('items_id');

        // gives the medications being monitered for the given user
        for ($i = 0; $i < count($medications_items); $i++) {
            //get id's of all medication items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $medications_items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
            if ($item_for_user[$i] != null) {
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $medications_categories[] = $user_items->items_text;
            }
        }//dd($medications_categories);

        //get all medication observations for the user

        $medication_obs = DB::connection('mysql_no_prefix')
            ->table('rules_questions')
            ->select('lv_observations.id', 'rules_items.items_text', 'lv_observations.obs_date', 'lv_observations.obs_value', 'lv_observations.obs_key', 'lv_observations.obs_message_id')
            ->join('lv_observations', 'rules_questions.msg_id', '=', 'lv_observations.obs_message_id')
            ->join('rules_items', 'rules_questions.qid', '=', 'rules_items.qid')
            ->where('user_id', $user->ID)
            ->where('lv_observations.obs_key', 'Adherence')
            ->where('lv_observations.obs_unit', '!=', 'invalid')
            ->where('lv_observations.obs_unit', '!=', 'scheduled')
            ->where('obs_unit', '!=', 'outbound')
            ->distinct('lv_observations.id')
            ->orderBy('lv_observations.id')
            ->get();

        //dd($medication_obs);

        //group observation readings by medicine

        $temp_meds = array();

        //Add scaffolding to sections

        $meds_array['Better']['description'] = '';
        $meds_array['Needs Work']['description'] = '';
        $meds_array['Worse']['description'] = '';

        foreach ($medications_categories as $category) {
            $yes = 0;
            $count = 0;
            foreach ($medication_obs as $obs) {
                if ($obs->items_text == $category) {
                    //$temp_meds[$category]['obs'][] = $obs; //(Will create arrays to categorize obs by medicine)
                    if (strtoupper($obs->obs_value) == 'Y') {
                        $yes++;
                    }
                    unset($medication_obs[$count]);
                    $count++;
                }
            }
            $temp_meds[$category]['yes'] = $yes;
            $temp_meds[$category]['total'] = $count;

            if ($temp_meds[$category]['yes'] != 0 && $temp_meds[$category]['total'] != 0) {
                //calculate ratio to tenth of a decimal place
                $temp_meds[$category]['percent'] = round($yes / $count, 1);
            } else {
                $temp_meds[$category]['percent'] = 0;
            }

            //add to categories based on percentage of responses
            switch ($temp_meds[$category]['percent']) {
                case ($temp_meds[$category]['percent'] > 0.8):
                    $meds_array['Better']['description'] .= ($meds_array['Better']['description'] == '' ? $category : ', ' . $category);
                    break;
                case ($temp_meds[$category]['percent'] >= 0.5):
                    $meds_array['Needs Work']['description'] .= ($meds_array['Needs Work']['description'] == '' ? $category : ', ' . $category);
                    break;
                case ($temp_meds[$category]['percent'] == 0):
                    $meds_array['Worse']['description'] .= ($meds_array['Worse']['description'] == '' ? $category : ', ' . $category);
                    break;
                default:
                    $meds_array['Worse']['description'] .= ($meds_array['Worse']['description'] == '' ? $category : ', ' . $category);
                    break;
            }
            //echo $category.': ' . $temp_meds[$category]['percent'] . ' <br /> ';
        }
        //dd($temp_meds); //Show all the medication categories and stats
        //dd(json_encode($medications)); // show the medications by adherence category

        $medications['Data'][0] = ['name' => $meds_array['Better']['description'],'Section' => 'Better'] ;
        $medications['Data'][1] = ['name' => $meds_array['Needs Work']['description'],'Section' => 'Needs Work'] ;
        $medications['Data'][2] = ['name' => $meds_array['Worse']['description'],'Section' => 'Worse'] ;



        //**************TRACKING CHANGES SECTION**************

        //get observations for user to calculate adherence
        $tracking_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Biometrics to Monitor')->first();
        $tracking_items = CPRulesItem::where('pcp_id', $tracking_pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
        // gives the biometrics being monitered for the given user
        for ($i = 0; $i < count($tracking_items); $i++) {
            //get id's of all biometrics items that are active for the given user
            $items_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
            if ($items_for_user[$i] != null) {
                //Find the items_text for the ones that are active
                $user_items = CPRulesItem::find($items_for_user[$i]->items_id);

                $tracking_q = CPRulesQuestions::find($user_items->qid);
                //get all the message_ids active for the user
                $tracking_obs_message_ids[] = $tracking_q->msg_id;

                //map obs_message_id => obs_key ("CF_RPT_50" => "Cigarettes")
                $tracking_obs_question_map[$tracking_q->msg_id] = str_replace('_', ' ', $tracking_q->obs_key);

                //get all the targets for biometrics that are being observed
                $target_items = CPRulesItem::where('items_parent', $items_for_user[$i]->items_id)->where('items_text', 'like', '%Target%')->get();
                foreach ($target_items as $target_item) {
                    $target_value = CPRulesUCP::where('items_id', $target_item->items_id)->where('user_id', $user->ID)->lists('meta_value');
                    $target_array[str_replace('_', ' ', $tracking_q->obs_key)] = $target_value[0];
                }
            }
        }//dd($tracking_obs_message_ids);

        $tracking_obs_data = array();
        array_reverse($tracking_obs_message_ids);
        foreach ($tracking_obs_message_ids as $q) {
            for ($i = 0; $i < 12; $i++) {

                $previous_week = strtotime("-" . $i . " week +1 day");
                $start_week = strtotime("last sunday midnight", $previous_week);
                $end_week = strtotime("next saturday 11:59:59pm", $start_week);
                $date_start = date("Y-m-d H:i:s", $start_week);
                $date_end = date("Y-m-d H:i:s", $end_week);

                if ($q == 'CF_RPT_20') {
                    $temp = DB::table('observations')
                        ->select(DB::raw('floor(avg(CAST(SUBSTRING_INDEX(obs_value, \'/\', 1) as UNSIGNED))) as Reading'))
                        ->where('obs_message_id', $q)
                        ->where('obs_unit', '!=', 'invalid')
                        //->where(DB::raw('obs_date >= '. $date_start . ' AND obs_date <=' . $date_end))
                        ->where('obs_date', '>=', $date_start)
                        ->where('obs_date', '<=', $date_end)
                        ->where('obs_unit', '!=', 'scheduled')
                        ->where('obs_unit', '!=', 'outbound')
                        ->where('obs_unit', '!=', 'outbound')
                        ->get();
                } else {
                    $temp = DB::table('observations')
                        ->select(DB::raw('floor(AVG(CAST(obs_value as UNSIGNED))) as Reading'))
                        ->where('user_id', $user->ID)
                        ->where('obs_message_id', $q)
                        ->where('obs_unit', '!=', 'invalid')
                        ->where('obs_date', '>=', $date_start)
                        ->where('obs_date', '<=', $date_end)
                        ->where('obs_unit', '!=', 'scheduled')
                        ->where('obs_unit', '!=', 'outbound')
                        ->where('obs_unit', '!=', 'outbound')
                        ->get();
                }



                $tracking_obs_data[$q][$i]['id'] = $i + 1;
                $tracking_obs_data[$q][$i]['week'] = date("n/j", $end_week);
                $tracking_obs_data[$q][$i]['Reading'] = $temp[0]->Reading == null ? 'No Readings' : $temp[0]->Reading;
                $tracking_obs_data[$q][$i]['unit'] = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);

            }
            //dd($tracking_obs_data[$q][0]);

            $num_obs = count($tracking_obs_data[$q]);
            $biometricData = ['progression' => '', 'color' => ''];

            if (!$tracking_obs_data[$q]) {
                return 'Error';
            }
            //store all the modified data in this array

            if ($tracking_obs_data[$q][0]['Reading'] != 'No Readings' && $tracking_obs_data[$q][1]['Reading'] != 'No Readings') {
                $change = abs($tracking_obs_data[$q][0]['Reading'] - $tracking_obs_data[$q][1]['Reading']);
                if ($tracking_obs_question_map[$q] == 'Cigarettes') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]['Reading'] > $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'Worse';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]['Reading'] == $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'No Change';
                        $progression = 'Unchanged';
                    } else {
                        $status = 'Better';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Blood Pressure') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]['Reading'] > $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'Worse';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]['Reading'] == $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'No Change';
                        $progression = 'Unchanged';
                    } else {
                        $status = 'Better';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Blood Sugar') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]['Reading'] > $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'Worse';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]['Reading'] == $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'No Change';
                        $progression = 'Unchanged';
                    } else {
                        $status = 'Better';
                        $progression = 'down';
                    }
                } else if ($tracking_obs_question_map[$q] == 'Weight') {
                    $unit = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
                    if ($tracking_obs_data[$q][0]['Reading'] > $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'Worse';
                        $progression = 'up';
                    } else if ($tracking_obs_data[$q][0]['Reading'] == $tracking_obs_data[$q][1]['Reading']) {
                        $status = 'No Change';
                        $progression = 'Unchanged';
                    } else {
                        $status = 'Better';
                        $progression = 'down';
                    }

                    if ($tracking_obs_data[$q][0]['Reading'] != 'No Readings' && $tracking_obs_data[$q][1]['Reading'] != 'No Readings')         {
                        var_dump($tracking_obs_data[$q][10]['Reading'] . '<br/>' . $tracking_obs_data[$q][11]['Reading'] . '<br/>' . str_replace(' ', '_', $tracking_obs_question_map[$q]) . '<br/>' . $target_array[$tracking_obs_question_map[$q]]);
                    } else {

                        $biometricData['color'] = 'yellow';
                        $biometricData['progression'] = 'Unchanged';
                    }

                }
            } else {
                $status = 'Unchanged';
                $progression = 'Unchanged';
                $unit = '';
                $change = 'Unchanged';
            }

            $biometricData = $this->biometricsIndicators($tracking_obs_data[$q][1]['Reading'],$tracking_obs_data[$q][0]['Reading'],str_replace(' ', '_', $tracking_obs_question_map[$q]),$target_array[$tracking_obs_question_map[$q]]);

            if(str_replace(' ', '_', $tracking_obs_question_map[$q]) == 'Cigarettes'){
                //dd( $biometricData);
                //dd($tracking_obs_data[$q][0]);
                //dd('First: '. $tracking_obs_data[$q][1]['Reading']. ' Last: '. $tracking_obs_data[$q][0]['Reading']);
            }

            $trackingChangesUnordered['Data'][] =
                [
                    'Biometric' => $tracking_obs_question_map[$q],
                    //'Latest Weekly Avg.' => $tracking_obs_data[$q][0]->avg,
                    'Status' => $status,
                    'Progression' => $biometricData['progression'],
                    'Color' => $biometricData['color'],
//                  'Color' => $colors[array_rand($colors)],
                    'Change: ' => $change . $unit,
                    'Latest Weekly Data' => $tracking_obs_data[$q][0]['Reading'] . $unit,
                    'Goal' => $target_array[$tracking_obs_question_map[$q]],
                    'data' => array_reverse($tracking_obs_data[$q])
                ];
            //, 'data' => $temp_meds];

        }

        //Reverse Order
        $count = count($trackingChangesUnordered['Data']);
        $trackingChanges['Data'] = array();

        for($i=$count-1;$i>=0;$i--){
            $trackingChanges['Data'][] =$trackingChangesUnordered['Data'][$i];
        }

        //dd($trackingChanges['Data']);

        // WRAPPING UP
        $progress['Progress_Report'][] = $userHeader;
        $progress['Progress_Report'][] = array_reverse($trackingChanges);
        $progress['Progress_Report'][] = $medications;

        return $progress;
    }

    public function careplan($id)
    {

        $user = WpUser::find($id);

        //=======================================
        //========WE ARE TREATING================
        //=======================================

        $treating['Section'] = 'We Are Treating';
        $treating['Data'] = array();

        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->first();
        //Get all the items for each section
        $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id');


        for ($i = 0; $i < count($items); $i++) {
            //get id's of all lifestyle items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
            $items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
            $items_detail_ucp[$i] = CPRulesUCP::where('items_id', $items_detail[$i]->items_id)->where('user_id', $user->ID)->first();
            if ($item_for_user[$i] != null) {
                $count = 0;
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $treating['Data'][] = ['name' => $user_items->items_text, 'comment' => ($items_detail_ucp[$i]->meta_value == '' ? 'Nothing' : $items_detail_ucp[$i]->meta_value)];
            }
        }

        if (count($treating['Data']) < 1) {
            $treating['Data'] = ['name' => 'None'];
        }

        //=======================================
        //========YOUR HEALTH GOALS==============
        //=======================================

        $goals['Section'] = 'Your Health Goals';
        $target_array = array();

        $tracking_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Biometrics to Monitor')->first();
        $tracking_items = CPRulesItem::where('pcp_id', $tracking_pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
        // gives the biometrics being monitered for the given user
        for ($i = 0; $i < count($tracking_items); $i++) {
            //get id's of all biometrics items that are active for the given user
            $items_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
            if ($items_for_user[$i] != null) {
                //Find the items_text for the ones that are active
                $user_items = CPRulesItem::find($items_for_user[$i]->items_id);

                $tracking_q = CPRulesQuestions::find($user_items->qid);
                //get all the message_ids active for the user
                $tracking_obs_message_ids[] = $tracking_q->msg_id;

                //map obs_message_id => obs_key ("CF_RPT_50" => "Cigarettes")
                $tracking_obs_question_map[$tracking_q->msg_id] = str_replace('_', ' ', $tracking_q->obs_key);

                // @todo setup starting reading from obsmeta

                //get all the targets for biometrics that are being observed
                $target_items = CPRulesItem::where('items_parent', $items_for_user[$i]->items_id)->where('items_text', 'like', '%Target%')->get();

                foreach ($target_items as $target_item) {
                    $target_value = CPRulesUCP::where('items_id', $target_item->items_id)->where('user_id', $user->ID)->lists('meta_value');
                    $target_array[str_replace('_', ' ', $tracking_q->obs_key)] = $target_value[0];
                }
            }
        }//dd($tracking_obs_message_ids);
        if (count($target_array) < 1) {
            $goals['Data'] = 'None';
        } else {
            foreach ($target_array as $key => $value) {
                $starting_val = Observation::getStartingObservation($id, $this->biometricsMessageIdMapping($key));
                $goals['Data'][] = ['name' => '<B>Lower ' . $key . ' to ' . $value . $this->biometricsUnitMapping($key) . ' </B> from  '.$starting_val . $this->biometricsUnitMapping($key)];
            }
        }

        //=======================================
        //======MONITORING MEDICATIONS===========
        //=======================================

        $monMedications['Section'] = 'Medications to Monitor';
        if ($this->getItemsForParent('Medications to Monitor', $user) != false) {
            $monMedications['Data'] = $this->getItemsForParent('Medications to Monitor', $user);
        } else {
            $none['name'] = 'None';
            $monMedications['Data'][] = $none;
        }

        //=======================================
        //========TAKING MEDICATIONS=============
        //=======================================

        $takMedications['Section'] = 'Medication Details';

        $additional_information_item = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Additional Information')->first();
        $medication_information_item = CPRulesItem::where('pcp_id', $additional_information_item->pcp_id)->where('items_parent', 0)->where('items_text', 'Medications List')->first();
        $medication_tracking_item = CPRulesItem::where('items_parent', $medication_information_item->items_id)->first();
        $medications_taking = CPRulesUCP::where('items_id', $medication_tracking_item->items_id)->where('user_id', $user->ID)->first();


        if ($medications_taking->meta_value != null) {
            $takMedications['Data'][] = ['name' => $medications_taking->meta_value];
        } else {
            $none = ['name' => 'None'];
            $takMedications['Data'][] = $none;
        }

        //=======================================
        //========SYMPTOMS TO MONITOR============
        //=======================================

            $symptoms['Section'] = 'Watch out for';
            if ($this->getItemsForParent('Symptoms to Monitor', $user) != false) {
                $symptoms['Data'] = $this->getItemsForParent('Symptoms to Monitor', $user);
            } else {
                $symptoms['Data'] = ['name' => 'None'];
            }

        //=======================================
        //========LIFESTYLE TO MONITOR===========
        //=======================================

            $lifestyle['Section'] = 'We Are Informing You About';

            if ($this->getItemsForParent('Lifestyle to Monitor', $user) != false) {
                $lifestyle['Data'] = $this->getItemsForParent('Lifestyle to Monitor', $user);
            } else {
                $lifestyle['Data'] = ['name' => 'None'];
            }

        //=======================================
        //===========CHECK IN PLAN===============
        //=======================================

            $userConfig = $user->userConfig();
            $check['Section'] = 'Check In Plan';
            $check['Description'] = 'We will check in with you at' . $userConfig['study_phone_number'] . ' every day at ' . $userConfig['preferred_contact_time'];

            $days = array('Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat');

            for ($i = 0; $i < count($days); $i++) {
                $check['Data'][] = ['day' => $days[$i], 'time' => $userConfig['preferred_contact_time']];
            }

        //=======================================
        //===========OTHER INFO===============
        //=======================================

            $other['Section'] = 'Other Information';

            $other['Data'] = array();
            $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Additional Information')->first();
            //Get all the items for each section
            $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id');

            for ($i = 0; $i < count($items); $i++) {
                //get id's of all lifestyle items that are active for the given user
                $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
                $items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
                $items_detail_ucp[$i] = CPRulesUCP::where('items_id', $items_detail[$i]->items_id)->where('user_id', $user->ID)->first();
                if ($item_for_user[$i] != null) {
                    $count = 0;
                    //Find the items_text for the one's that are active
                    $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                    $other['Data'][] = ['name' => $user_items->items_text, 'comment' => ($items_detail_ucp[$i]->meta_value == '' ? 'Nothing' : $items_detail_ucp[$i]->meta_value)];
                }
            }

            if (count($other['Data']) < 1) {
                $other['Data'] = ['name' => 'None'];
            }


            //ADD ALL TO MAIN ARRAY

            $careplan['CarePlan_Report'][] = $this->reportHeader($id);
            $careplan['CarePlan_Report'][] = $treating;
            $careplan['CarePlan_Report'][] = $goals;
            $careplan['CarePlan_Report'][] = $monMedications;
            $careplan['CarePlan_Report'][] = $takMedications;
            $careplan['CarePlan_Report'][] = $symptoms;
            $careplan['CarePlan_Report'][] = $check;
            $careplan['CarePlan_Report'][] = $other;

            return $careplan;

        }
}