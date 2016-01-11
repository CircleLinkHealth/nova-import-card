<?php namespace App\Services;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesQuestions;
use App\CPRulesUCP;
use App\Location;
use App\Observation;
use App\Services\CareplanUIService;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

Class ReportsService
{
    //Progress Report API

    public function reportHeader($id)
    {
        debug($id);
        $user = User::find($id);
        $user_meta = UserMeta::where('user_id', '=', $user->ID)->lists('meta_value', 'meta_key');
        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user_meta['first_name'] . ' ' . $user_meta['last_name'];
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = User::findOrFail($userConfig['billing_provider']);
        $providerConfig = $provider->userConfig();
        $provider_meta = UserMeta::where('user_id', '=', $provider->ID)->lists('meta_value', 'meta_key');
        $userHeader['Provider_Name'] = trim($providerConfig['prefix'] . ' ' . $provider_meta['first_name'] . ' ' . $provider_meta['last_name'] . ' ' . $providerConfig['qualification']);
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($userConfig['preferred_contact_location']);

        return $userHeader;
    }

    public static function getItemsForParent($item, User $user)
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
     * Returns an Array with Color, Unit, Status and Arrow for a given Biometric,
     * and a pair of reading, Usually First Week and Last Week
     * @param $weeklyReading1 - most recent
     * @param $weeklyReading2 - second most recent
     * @param $biometric
     * @param $target
     * @return array
     */
    public function biometricsIndicators($weeklyReading1, $weeklyReading2, $biometric, $target){

        //Difference in most recent weekly values
        $change = $weeklyReading1 - $weeklyReading2;
        //var_dump($biometric. ': '. $change);

        //result array
        $changes_array = array();
        $changes_array['unit'] = $this->biometricsUnitMapping(str_replace('_', ' ',$biometric));
        $changes_array['change'] = abs($change);

        //max and min for BP and BS
        if ($biometric == 'Blood_Sugar') {
            $max = 141; $min = 80;
        } else if($biometric == 'Blood_Pressure') {
            $max = 141; $min = 100;
            $target = explode('/', $target);
            $target = $target[0];
        }

        $target = intval($target);

        if($weeklyReading1 < $weeklyReading2) {
            $changes_array['progression'] = 'down';
        } else if($weeklyReading1 > $weeklyReading2) {
            $changes_array['progression'] = 'up';
        } else {
            $changes_array['progression'] = 'unchanged'; // no arrow
            $changes_array['color'] = 'yellow';
        }

        if($biometric == 'Blood_Sugar' || $biometric == 'Blood_Pressure') {
        // Color is decided by whether the reading falls between these values
        // per old UI

            // within range, green good
            if ($weeklyReading1 > $min && $weeklyReading1 < $max) {
                $changes_array['color'] = 'green';
                $changes_array['status'] = 'Better';
            }

            // outside of range
            if (!isset($changes_array['color'])) {
                // if current reading is ABOVE target reading
                if ($weeklyReading1 > $target) {
                    if ($change < 0) { // over goal and dropping
                        $changes_array['color'] = 'green';
                        $changes_array['status'] = 'Better';
                    } else if ($change > 0) { // over goal and rising
                        $changes_array['color'] = 'red';
                        $changes_array['status'] = 'Worse';
                    }
                }
                // if current reading is BELOW target reading
                if ($weeklyReading1 < $target) {
                    if ($change > 0) { // under goal and rising
                        $changes_array['color'] = 'green';
                        $changes_array['status'] = 'Better';
                    } else if ($change < 0) { // under goal and dropping
                        $changes_array['color'] = 'red';
                        $changes_array['status'] = 'Worse';
                    }
                }
            }
        } else if($biometric == 'Weight') {

            if ($weeklyReading1 < $target) { // under weight target
                if($change < 0){
                    // lost weight
                    $changes_array['color'] = 'red';
                    $changes_array['status'] = 'Worse';
                } else {
                    //gained weight
                    $changes_array['color'] = 'green';
                    $changes_array['status'] = 'Better';
                }
            } else if ($weeklyReading1 > $target) { // over weight
                if($change <= 0){
                    // lost weight
                    $changes_array['color'] = 'green';
                    $changes_array['status'] = 'Better';
                } else {
                    // gained weight
                    $changes_array['color'] = 'red';
                    $changes_array['status'] = 'Worse';
                }
            }
        } else if($biometric == 'Cigarettes') {
            if ($weeklyReading1 < $target) { // latest reading is below target smokes
                if($change < 0){
                    // smoked lesser
                    $changes_array['color'] = 'green';
                    $changes_array['status'] = 'Better';
                } else {
                    //smoked more
                    $changes_array['color'] = 'red';
                    $changes_array['status'] = 'Worse';
                }
            } else if ($weeklyReading1 > $target) { // smoked more
                if($change < 0){
                    // smoked lesser
                    $changes_array['color'] = 'green';
                    $changes_array['status'] = 'Better';
                } else {
                    //smoked more
                    $changes_array['color'] = 'red';
                    $changes_array['status'] = 'Worse';
                }
            }
        }
        return $changes_array;
    }

    public function medicationStatus(User $user, $fromApp = true){

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
        if ($fromApp) {
            $meds_array['Better']['description'] = '';
            $meds_array['Needs Work']['description'] = '';
            $meds_array['Worse']['description'] = '';
        } else {
            $meds_array['Better']['description'] = array();
            $meds_array['Needs Work']['description'] = array();
            $meds_array['Worse']['description'] = array();
        }

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
            if ($fromApp) {
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
            } else {
                // for provider UI
                switch ($temp_meds[$category]['percent']) {
                    case ($temp_meds[$category]['percent'] > 0.8):
                        $meds_array['Better']['description'][] = $category;
                        break;
                    case ($temp_meds[$category]['percent'] >= 0.5):
                        $meds_array['Needs Work']['description'][] = $category;
                        break;
                    case ($temp_meds[$category]['percent'] == 0):
                        $meds_array['Worse']['description'][] = $category;
                    default:
                        $meds_array['Worse']['description'][] = $category;
                        break;
                }
            }
            //dd($temp_meds); //Show all the medication categories and stats
            //dd(json_encode($medications)); // show the medications by adherence category
        }
        $medications[0] = ['name' => $meds_array['Better']['description'],'Section' => 'Better'] ;
        $medications[1] = ['name' => $meds_array['Needs Work']['description'],'Section' => 'Needs Work'] ;
        $medications[2] = ['name' => $meds_array['Worse']['description'],'Section' => 'Worse'] ;

    return $medications;

    }

    public function progress($id)
    {

        $user = User::find($id);

        //main container

        $progress = array();
        $userHeader = $this->reportHeader($id);
        $trackingChanges = array();
        $medications = array();

        $trackingChanges['Section'] = 'Tracking Changes';

        //**************TAKING YOUR MEDICATIONS SECTION**************

        $medications['Section'] = 'Taking your <b>Medications</b>?';
        $medications['Data'] = $this->medicationStatus($user);

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

                $biometricData = $this->biometricsIndicators($tracking_obs_data[$q][0]['Reading'],$tracking_obs_data[$q][1]['Reading'],str_replace(' ', '_', $tracking_obs_question_map[$q]),$target_array[$tracking_obs_question_map[$q]]);

            } else {
                $biometricData['status'] = 'Unchanged';
                $biometricData['unit'] = '';
                $biometricData['change'] = 'Unchanged';
                $biometricData['color'] = 'yellow';
                $biometricData['progression'] = 'Unchanged';
            }

            $trackingChangesUnordered['Data'][] =
                [
                    'Biometric' => $tracking_obs_question_map[$q],
                    //'Latest Weekly Avg.' => $tracking_obs_data[$q][0]->avg,
                    'Status' => $biometricData['status'],
                    'Progression' => $biometricData['progression'],
                    'Color' => $biometricData['color'],
                    'Change: ' => $biometricData['change'] . $biometricData['unit'],
                    'Latest Weekly Data' => $tracking_obs_data[$q][0]['Reading'] . $biometricData['unit'],
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

        $user = User::find($id);

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
        $time = microtime(true);
        $goals['Section'] = 'Your Health Goals';
        $progression = '';

        $goals_active_biometrics = array();

        $goals_raw = (new CareplanUIService())->getCareplanSectionData($user->blogId(), 'Biometrics to Monitor', $user);
        //dd($goals_raw['sub_meta']['Biometrics to Monitor']);
        foreach ($goals_raw['sub_meta']['Biometrics to Monitor'][0] as $key => $value){
            if($value['item_status'] == 'Active'){
                $goals_active_biometrics[$key] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key];
//                switch($goals_raw['sub_meta']['Biometrics to Monitor'][$key]){
//                    case 'Weight':
//                        $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Weight']['value'];
//                        break;
                }
                if($key == 'Weight'){
                    $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Weight']['value'];
                    $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target Weight']['value'];
                }

                if ($key == 'Blood Sugar'){
                    $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting BS']['value'];
                    $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target BS']['value'];
                }

                if ($key == 'Blood Pressure'){
                    $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting BP']['value'];
                    $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target BP']['value'];
                }

                if ($key == 'Smoking (# per day)'){
                    $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Count']['value'];
                    $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target Count']['value'];
                }

                if($goals_active_biometrics[$key]['target'] > $goals_active_biometrics[$key]['starting']){
                    $progression = 'Raise ';
                } else if (
                    $goals_active_biometrics[$key]['target'] <= $goals_active_biometrics[$key]['starting']){
                    $progression = 'Lower ';
                }


            $goals['Data'][] = ['name' => '<B>' . $progression . $key . ' to ' . $goals_active_biometrics[$key]['target'] . $this->biometricsUnitMapping($key) . ' </B> from  '.$goals_active_biometrics[$key]['starting'] . $this->biometricsUnitMapping($key)];

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

        $lifestyle['Section'] = 'Informing You About';

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