<?php namespace App\Services;

use App\CarePlan;
use App\ForeignId;
use App\Location;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\Cpm;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmMisc;
use App\Note;
use App\PatientReports;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;


class ReportsService
{

    //FOR MOBILE API
    public static function biometricsMessageIdMapping($biometric)
    {
        switch ($biometric) {
            case 'Blood Sugar':
            case 'Blood_Sugar':
                return 'CF_RPT_30';
                break;
            case 'Cigarettes':
                return 'CF_RPT_50';
                break;
            case 'Weight':
                return 'CF_RPT_40';
                break;
            case 'Blood Pressure':
            case 'Blood_Pressure':
                return 'CF_RPT_20';
                break;
            default:
                return '';
        }
    }

    public function reportHeader($id)
    {
        $user = User::find($id);
        $user_meta = UserMeta::where('user_id', '=', $user->id)->pluck('meta_value', 'meta_key')->all();
        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user_meta['first_name'] . ' ' . $user_meta['last_name'];
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = User::findOrFail($user->billingProviderID);
        $providerConfig = $provider->userConfig();
        $provider_meta = UserMeta::where('user_id', '=', $provider->id)->pluck('meta_value', 'meta_key')->all();
        $userHeader['Provider_Name'] = trim($providerConfig['prefix'] . ' ' . $provider_meta['first_name'] . ' ' . $provider_meta['last_name'] . ' ' . $providerConfig['qualification']);
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($userConfig['preferred_contact_location']);

        return $userHeader;
    }

    public function getBiometricsToMonitor(User $user)
    {
        return $user->cpmBiometrics()->get()->pluck('name')->all();
    }

    public function getProblemsToMonitor(User $user)
    {
        if (!$user) {
            throw new Exception('User not found..');
        }

        return $user->cpmProblems()->get()->pluck('name')->all();
    }

    public function getSymptomsToMonitor(CarePlan $carePlan)
    {
        $temp = [];
        if ($carePlan) {
            foreach ($carePlan->careSections as $section) {
                if ($section->name == 'symptoms-to-monitor') {
                    foreach ($section->carePlanItems as $item) {
                        if ($item->meta_value == 'Active') {
                            $temp[] = $item->careItem->display_name;
                        }
                    }
                }
            }
        }

        return $temp;
    }

    public function getLifestyleToMonitor(CarePlan $carePlan)
    {
        $temp = [];
        if ($carePlan) {
            foreach ($carePlan->careSections as $section) {
                if ($section->name == 'lifestyle-to-monitor') {
                    foreach ($section->carePlanItems as $item) {
                        if ($item->meta_value == 'Active') {
                            $temp[] = $item->careItem->display_name;
                        }
                    }
                }
            }
        }

        return $temp;
    }

    public function medicationsList(User $user)
    {
        $medications = $user->cpmMedicationGroups()->get()->pluck('name')->all();

        return $medications;
    }

    public function getMedicationStatus(
        User $user,
        $fromApp = true
    ) {
        $medications_categories = $user->cpmMedicationGroups()->get()->pluck('name')->all();

        //get all medication observations for the user
        $medication_obs = DB::connection('mysql_no_prefix')
            ->table('rules_questions')
            ->select('lv_observations.id', 'rules_items.items_text', 'lv_observations.obs_date',
                'lv_observations.obs_value', 'lv_observations.obs_key', 'lv_observations.obs_message_id')
            ->join('lv_observations', 'rules_questions.msg_id', '=', 'lv_observations.obs_message_id')
            ->join('rules_items', 'rules_questions.qid', '=', 'rules_items.qid')
            ->where('user_id', $user->id)
            ->where('lv_observations.obs_key', 'Adherence')
            ->where('lv_observations.obs_unit', '!=', 'invalid')
            ->where('lv_observations.obs_unit', '!=', 'scheduled')
            ->where('obs_unit', '!=', 'outbound')
            ->distinct('lv_observations.id')
            ->orderBy('lv_observations.id')
            ->get();

        //group observation readings by medicine
        $temp_meds = [];
        foreach ($medications_categories as $cat) {
            $temp_meds[$cat]['total'] = 0;
            $temp_meds[$cat]['yes'] = 0;
        }

        //Add scaffolding to sections
        if ($fromApp) {
            $meds_array['Better']['description'] = '';
            $meds_array['Needs Work']['description'] = '';
            $meds_array['Worse']['description'] = '';
        } else {
            $meds_array['Better']['description'] = [];
            $meds_array['Needs Work']['description'] = [];
            $meds_array['Worse']['description'] = [];
        }

        foreach ($medication_obs as $obs) {
            $yes = 0;
            $count = 0;
            if (in_array($obs->items_text, $medications_categories)) {
                $temp_meds[$obs->items_text]['total']++;
                if ($obs->obs_value == 'Y') {
                    $temp_meds[$obs->items_text]['yes']++;
                }
            }
        }

        //Remove meds with no observations
        foreach ($temp_meds as $key => $value) {
            if ($value['total'] == 0) {
                unset($temp_meds[$key]);
            }
        }

        foreach ($temp_meds as $key => $value) {
            $yes = $value['yes'];
            $total = $value['total'];

            if ($yes != 0 && $total != 0) {
                $adhereance_percent = doubleval($yes / $total);
            } else {
                if ($yes == 0 && $total == 1) {
                    $adhereance_percent = 0;
                } else {
                    if ($yes == 0 && $total == 0) {
                        $adhereance_percent = 1;
                    } else {
                        if ($yes == 0) {
                            $adhereance_percent = 0;
                        }
                    }
                }
            }
//            if ($fromApp) {
//                //add to categories based on percentage of responses
//                switch ($adhereance_percent) {
//                    case ($adhereance_percent > 0.8):
//                        $meds_array['Better']['description'] .= ($meds_array['Better']['description'] == '' ? $category : ', ' . $category);
//                        break;
//                    case ($temp_meds[$category]['percent'] >= 0.5):
//                        $meds_array['Needs Work']['description'] .= ($meds_array['Needs Work']['description'] == '' ? $category : ', ' . $category);
//                        break;
//                    case ($temp_meds[$category]['percent'] == 0.0):
//                        $meds_array['Worse']['description'] .= ($meds_array['Worse']['description'] == '' ? $category : ', ' . $category);
//                        break;
//                    default:
//                        $meds_array['Worse']['description'] .= ($meds_array['Worse']['description'] == '' ? $category : ', ' . $category);
//                        break;
//                }
//                dd($category.': ' . $temp_meds[$category]['percent'] . ' <br /> ');
//            } else {
            // for provider UI
            switch (true) {
                case ($adhereance_percent > 0.8):
                    $meds_array['Better']['description'][] = $key;
                    break;
                case ($adhereance_percent >= 0.5):
                    $meds_array['Needs Work']['description'][] = $key;
                    break;
                case ($adhereance_percent < 0.5):
                    $meds_array['Worse']['description'][] = $key;
                    break;
                default:
                    $meds_array['Worse']['description'][] = $key;
                    break;
            }
        }
        //Show all the medication categories and stats
        //dd(json_encode($medications)); // show the medications by adherence category

        $medications[0] = [
            'name'    => $meds_array['Better']['description'],
            'Section' => 'Better',
        ];
        $medications[1] = [
            'name'    => $meds_array['Needs Work']['description'],
            'Section' => 'Needs Work',
        ];
        $medications[2] = [
            'name'    => $meds_array['Worse']['description'],
            'Section' => 'Worse',
        ];

        return $medications;

    }

    public function getTargetValueForBiometric(
        $biometric,
        User $user,
        $showUnits = true
    ) {
        $bio = CpmBiometric::whereName(str_replace('_', ' ', $biometric))->first();
        $biometric_values = app(config('cpmmodelsmap.biometrics')[$bio->type])->getUserValues($user);

        if ($showUnits) {
            return $biometric_values['target'] . ReportsService::biometricsUnitMapping($biometric);
        } else {
            return $biometric_values['target'];
        }
    }

    public static function biometricsUnitMapping($biometric)
    {
        switch ($biometric) {
            case 'Blood Sugar':
            case 'Blood_Sugar':
                return ' mg/dL';
                break;
            case 'Cigarettes':
                return '';
                break;
            case 'Weight':
                return ' lbs';
                break;
            case 'Blood Pressure':
            case 'Blood_Pressure':
                return ' mm Hg';
                break;
            default:
                return '';
        }
    }

    public function getBiometricsData(
        $biometric,
        $user
    ) {
        $data = DB::table('lv_observations')
            ->select(DB::raw('user_id, replace(obs_key,\'_\',\' \') \'Observation\',
					week(obs_date) week, year(obs_date) year, floor(datediff(now(), obs_date)/7) realweek,
					date_format(max(obs_date), \'%c/%e\') as day, date_format(min(obs_date), \'%c/%e\') as day_low,
					min(obs_date) as min_obs_id, max(obs_date) as obs_id,
					round(avg(obs_value)) \'Avg\''))
            ->where('obs_key', '=', $biometric)
            ->where('user_id', $user->id)
            ->where(DB::raw('datediff(now(), obs_date)/7'), '<=', 11)
            ->where('obs_unit', '!=', 'invalid')
            ->where('obs_unit', '!=', 'scheduled')
            ->groupBy('user_id')
            ->groupBy('obs_key')
            ->groupBy('realweek')
            ->orderBy('obs_date')
            ->get();

        return ($data)
            ? $data
            : '';

    }

    public function biometricsIndicators(
        $weeklyReading1,
        $weeklyReading2,
        $biometric,
        $target
    ) {//debug($biometric);

        if ($biometric == 'Blood Sugar') {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
            return $this->analyzeBloodSugar($weeklyReading1, $weeklyReading2);
        } else {
            if ($biometric == 'Blood Pressure') {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
                return $this->analyzeBloodPressure($weeklyReading1, $weeklyReading2);
            } else {
                if ($biometric == 'Weight') {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
                    return $this->analyzeWeight($weeklyReading1, $weeklyReading2);
                }
            }
        }
    }

    public function analyzeBloodSugar(
        $weeklyReading1,
        $weeklyReading2
    ) {
        $change = $weeklyReading1 - $weeklyReading2;

        if ($weeklyReading1 > 130) {
            if ($change > 0) { //The weekly average has increased
                $color = 'red';
                $progression = 'up';
                $copy = 'Worse';
            } else {
                if ($change < 0) { //The weekly average has decreased
                    $color = 'green';
                    $progression = 'down';
                    $copy = 'Better';
                } else { //The weekly average is unchanged
                    $color = 'yellow';
                    $copy = 'Unchanged';
                    $progression = '';
                }
            }
        } else {
            if ($weeklyReading1 <= 130 && $weeklyReading1 > 70) {
                $color = "green";
                $copy = "Good";
                if ($change > 0) { //The weekly average has increased
                    $progression = 'up';
                } else {
                    if ($change < 0) {
                        $progression = 'down';
                    } else {
                        $progression = '';
                    }
                }
            } else {
                if ($weeklyReading1 <= 70 && $weeklyReading1 > 60) {
                    $color = "yellow";
                    $copy = "Low";
                    if ($change > 0) { //The weekly average has increased
                        $progression = 'up';
                    } else {
                        if ($change < 0) {
                            $progression = 'down';
                        } else {
                            $progression = '';
                        }
                    }
                } else {
                    if ($weeklyReading1 <= 60) {
                        $color = "red";
                        $copy = "Too Low";
                        if ($change > 0) { //The weekly average has increased
                            $progression = 'up';
                        } else {
                            if ($change < 0) {
                                $progression = 'down';
                            } else {
                                $progression = '';
                            }
                        }
                    }
                }
            }
        }

        $changes_array = [];
        $changes_array['change'] = $change;
        $changes_array['unit'] = $this->biometricsUnitMapping("Blood Sugar");
        $changes_array['color'] = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status'] = $copy;

        return $changes_array;
    }

    public function analyzeBloodPressure(
        $weeklyReading1,
        $weeklyReading2
    ) {
        $change = $weeklyReading1 - $weeklyReading2;

        if ($weeklyReading1 > 130) {
            if ($change > 0) { //The weekly average has increased
                $color = 'red';
                $progression = 'up';
                $copy = 'Worse';
            } else {
                if ($change < 0) {
                    $color = 'green';
                    $progression = 'down';
                    $copy = 'Better';
                } else {
                    $color = 'yellow';
                    $copy = 'Unchanged';
                    $progression = '';
                }
            }
        } else {
            if ($weeklyReading1 <= 130 && $weeklyReading1 > 100) {
                $color = "green";
                $copy = "Good";
                if ($change > 0) { //The weekly average has increased
                    $progression = 'up';
                } else {
                    if ($change < 0) {
                        $progression = 'down';
                    } else {
                        $progression = '';
                    }
                }
            } else {
                if ($weeklyReading1 <= 100) {
                    $color = "yellow";
                    $copy = "Low";
                    if ($change > 0) { //The weekly average has increased
                        $progression = 'up';
                    } else {
                        if ($change < 0) {
                            $progression = 'down';
                        } else {
                            $progression = '';
                        }
                    }
                }
            }
        }

        $changes_array = [];
        $changes_array['change'] = $change;
        $changes_array['unit'] = $this->biometricsUnitMapping("Blood Pressure");
        $changes_array['color'] = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status'] = $copy;

        return $changes_array;
    }

    public function analyzeWeight(
        $weeklyReading1,
        $weeklyReading2
    ) {
        $change = $weeklyReading1 - $weeklyReading2;
        if ($change > 0) {
            $color = 'grey';
            $progression = 'up';
            $copy = 'Increased';
        } else {
            if ($change < 0) {
                $color = 'grey';
                $progression = 'down';
                $copy = 'Decreased';
            } else {
                $color = 'grey';
                $copy = 'Unchanged';
                $progression = '';

            }
        }
        $changes_array = [];
        $changes_array['change'] = $change;
        $changes_array['unit'] = $this->biometricsUnitMapping("Weight");
        $changes_array['color'] = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status'] = $copy;

        return $changes_array;
    }

    public function createAprimaPatientCarePlanPdfReport(
        $user,
        $provider_id
    ) {
        $file_name = $this->makePdfCareplan($user);

        $base_64_report = base64_encode(file_get_contents($file_name));

        $locationId = $user->getpreferredContactLocationAttribute();

        if (empty($locationId)) {
            return false;
        }

        try {
            //get foreign provider id
            $foreign_id = ForeignId::where('user_id', $provider_id)->where('system', ForeignId::APRIMA)->first();

            //update the foreign id to include a location as well
            if (empty($foreign_id->location_id)) {
                $foreign_id->location_id = $locationId;
                $foreign_id->save();
            }

        } catch (\Exception $e) {
            \Log::error("No foreign Id found when creating report. Message: $e->getMessage(). Code: $e->getCode()");

            return;
        }

        if (empty($foreign_id)) {
            \Log::error("Patient with UserId $user->id has no Aprima ProviderId");

            return;
        }

        $patientReport = PatientReports::create([
            'patient_id'  => $user->id,
            'patient_mrn' => $user->getMRNAttribute(),
            'provider_id' => $foreign_id->foreign_id,
            'file_type'   => PatientReports::CAREPLAN,
            'file_base64' => $base_64_report,
            'location_id' => $locationId,
        ]);
    }

    //Not compatible with current version
    // @todo refactor mobile apis
//    public function progress($id)
//    {
//
//        $user = User::find($id);
//
//        //main container
//
//        $progress = array();
//        $userHeader = $this->reportHeader($id);
//        $trackingChanges = array();
//        $medications = array();
//
//        $trackingChanges['Section'] = 'Tracking Changes';
//
//        //**************TAKING YOUR MEDICATIONS SECTION**************
//
//        $medications['Section'] = 'Taking your <b>Medications</b>?';
//        $medications['Data'] = $this->getMedicationStatus($user);
//
//        //**************TRACKING CHANGES SECTION**************
//
//        //get observations for user to calculate adherence
//        $tracking_pcp = CPRulesPCP::where('prov_id', '=', $user->program_id)->where('status', '=', 'Active')->where('section_text', 'Biometrics to Monitor')->first();
//        $tracking_items = CPRulesItem::where('pcp_id', $tracking_pcp->pcp_id)->where('items_parent', 0)->pluck('items_id')->all();
//        // gives the biometrics being monitered for the given user
//        for ($i = 0; $i < count($tracking_items); $i++) {
//            //get id's of all biometrics items that are active for the given user
//            $items_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items[$i])->where('meta_value', 'Active')->where('user_id', $user->id)->first();
//            if ($items_for_user[$i] != null) {
//                //Find the items_text for the ones that are active
//                $user_items = CPRulesItem::find($items_for_user[$i]->items_id);
//
//                $tracking_q = CPRulesQuestions::find($user_items->qid);
//                //get all the message_ids active for the user
//                $tracking_obs_message_ids[] = $tracking_q->msg_id;
//
//                //map obs_message_id => obs_key ("CF_RPT_50" => "Cigarettes")
//                $tracking_obs_question_map[$tracking_q->msg_id] = str_replace('_', ' ', $tracking_q->obs_key);
//
//                //get all the targets for biometrics that are being observed
//                $target_items = CPRulesItem::where('items_parent', $items_for_user[$i]->items_id)->where('items_text', 'like', '%Target%')->get();
//                foreach ($target_items as $target_item) {
//                    $target_value = CPRulesUCP::where('items_id', $target_item->items_id)->where('user_id', $user->id)->pluck('meta_value')->all();
//                    $target_array[str_replace('_', ' ', $tracking_q->obs_key)] = $target_value[0];
//                }
//            }
//        }//dd($tracking_obs_message_ids);
//
//        $tracking_obs_data = array();
//        array_reverse($tracking_obs_message_ids);
//        foreach ($tracking_obs_message_ids as $q) {
//            for ($i = 0; $i < 12; $i++) {
//
//                $previous_week = strtotime("-" . $i . " week +1 day");
//                $start_week = strtotime("last sunday midnight", $previous_week);
//                $end_week = strtotime("next saturday 11:59:59pm", $start_week);
//                $date_start = date("Y-m-d H:i:s", $start_week);
//                $date_end = date("Y-m-d H:i:s", $end_week);
//
//                if ($q == 'CF_RPT_20') {
//                    $temp = DB::table('lv_observations')
//                        ->select(DB::raw('floor(avg(CAST(SUBSTRING_INDEX(obs_value, \'/\', 1) as UNSIGNED))) as Reading'))
//                        ->where('obs_message_id', $q)
//                        ->where('obs_unit', '!=', 'invalid')
//                        //->where(DB::raw('obs_date >= '. $date_start . ' AND obs_date <=' . $date_end))
//                        ->where('obs_date', '>=', $date_start)
//                        ->where('obs_date', '<=', $date_end)
//                        ->where('obs_unit', '!=', 'scheduled')
//                        ->where('obs_unit', '!=', 'outbound')
//                        ->where('obs_unit', '!=', 'outbound')
//                        ->get();
//                } else {
//                    $temp = DB::table('lv_observations')
//                        ->select(DB::raw('floor(AVG(CAST(obs_value as UNSIGNED))) as Reading'))
//                        ->where('user_id', $user->id)
//                        ->where('obs_message_id', $q)
//                        ->where('obs_unit', '!=', 'invalid')
//                        ->where('obs_date', '>=', $date_start)
//                        ->where('obs_date', '<=', $date_end)
//                        ->where('obs_unit', '!=', 'scheduled')
//                        ->where('obs_unit', '!=', 'outbound')
//                        ->where('obs_unit', '!=', 'outbound')
//                        ->get();
//                }
//
//
//                $tracking_obs_data[$q][$i]['id'] = $i + 1;
//                $tracking_obs_data[$q][$i]['week'] = date("n/j", $end_week);
//                $tracking_obs_data[$q][$i]['Reading'] = $temp[0]->Reading == null ? 'No Readings' : $temp[0]->Reading;
//                $tracking_obs_data[$q][$i]['unit'] = $this->biometricsUnitMapping($tracking_obs_question_map[$q]);
//
//            }
//            //dd($tracking_obs_data[$q][0]);
//
//            $num_obs = count($tracking_obs_data[$q]);
//            $biometricData = ['progression' => '', 'color' => ''];
//
//            if (!$tracking_obs_data[$q]) {
//                return 'Error';
//            }
//            //store all the modified data in this array
//
//            if ($tracking_obs_data[$q][0]['Reading'] != 'No Readings' && $tracking_obs_data[$q][1]['Reading'] != 'No Readings') {
//
//                $biometricData = $this->biometricsIndicators($tracking_obs_data[$q][0]['Reading'], $tracking_obs_data[$q][1]['Reading'], str_replace(' ', '_', $tracking_obs_question_map[$q]), $target_array[$tracking_obs_question_map[$q]]);
//
//            } else {
//                $biometricData['status'] = 'Unchanged';
//                $biometricData['unit'] = '';
//                $biometricData['change'] = 'Unchanged';
//                $biometricData['color'] = 'yellow';
//                $biometricData['progression'] = 'Unchanged';
//            }
//
//            $trackingChangesUnordered['Data'][] =
//                [
//                    'Biometric' => $tracking_obs_question_map[$q],
//                    //'Latest Weekly Avg.' => $tracking_obs_data[$q][0]->avg,
//                    'Status' => $biometricData['status'],
//                    'Progression' => $biometricData['progression'],
//                    'Color' => $biometricData['color'],
//                    'Change: ' => $biometricData['change'] . $biometricData['unit'],
//                    'Latest Weekly Data' => $tracking_obs_data[$q][0]['Reading'] . $biometricData['unit'],
//                    'Goal' => $target_array[$tracking_obs_question_map[$q]],
//                    'data' => array_reverse($tracking_obs_data[$q])
//                ];
//            //, 'data' => $temp_meds];
//
//        }
//
//        //Reverse Order
//        $count = count($trackingChangesUnordered['Data']);
//        $trackingChanges['Data'] = array();
//
//        for ($i = $count - 1; $i >= 0; $i--) {
//            $trackingChanges['Data'][] = $trackingChangesUnordered['Data'][$i];
//        }
//
//        //dd($trackingChanges['Data']);
//
//        // WRAPPING UP
//        $progress['Progress_Report'][] = $userHeader;
//        $progress['Progress_Report'][] = array_reverse($trackingChanges);
//        $progress['Progress_Report'][] = $medications;
//
//        return $progress;
//    }
//    public function careplan($id)
//    {
//
//        $user = User::find($id);
//
//        //=======================================
//        //========WE ARE TREATING================
//        //=======================================
//
//        $treating['Section'] = 'We Are Treating';
//        $treating['Data'] = array();
//
//        //PCP has the sections for each provider, get all sections for the user's blog
//        $pcp = CPRulesPCP::where('prov_id', '=', $user->program_id)->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->first();
//
//        //Get all the items for each section
//        $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->pluck('items_id')->all();
//        for ($i = 0; $i < count($items); $i++) {
//            //get id's of all lifestyle items that are active for the given user
//            $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->id)->first();
//            $items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
//            $items_detail_ucp[$i] = CPRulesUCP::where('items_id', $items_detail[$i]->items_id)->where('user_id', $user->id)->first();
//            if ($item_for_user[$i] != null) {
//                $count = 0;
//                //Find the items_text for the one's that are active
//                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
//                $treating['Data'][] = ['name' => $user_items->items_text, 'comment' => ($items_detail_ucp[$i]->meta_value == '' ? 'Nothing' : $items_detail_ucp[$i]->meta_value)];
//            }
//        }
//
//        if (count($treating['Data']) < 1) {
//            $treating['Data'] = ['name' => 'None'];
//        }
//
//
//        //=======================================
//        //========YOUR HEALTH GOALS==============
//        //=======================================
//        $time = microtime(true);
//        $goals['Section'] = 'Your Health Goals';
//        $progression = '';
//
//        $goals_active_biometrics = array();
//
//        $goals_raw = (new CareplanUIService())->getCareplanSectionData($user->program_id, 'Biometrics to Monitor', $user);
//        //dd($goals_raw['sub_meta']['Biometrics to Monitor']);
//        foreach ($goals_raw['sub_meta']['Biometrics to Monitor'][0] as $key => $value) {
//            if ($value['item_status'] == 'Active') {
//                $goals_active_biometrics[$key] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key];
////                switch($goals_raw['sub_meta']['Biometrics to Monitor'][$key]){
////                    case 'Weight':
////                        $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Weight']['value'];
////                        break;
//            }
//            if ($key == 'Weight') {
//                $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Weight']['value'];
//                $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target Weight']['value'];
//            }
//
//            if ($key == 'Blood Sugar') {
//                $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting BS']['value'];
//                $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target BS']['value'];
//            }
//
//            if ($key == 'Blood Pressure') {
//                $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting BP']['value'];
//                $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target BP']['value'];
//            }
//
//            if ($key == 'Smoking (# per day)') {
//                $goals_active_biometrics[$key]['starting'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Starting Count']['value'];
//                $goals_active_biometrics[$key]['target'] = $goals_raw['sub_meta']['Biometrics to Monitor'][$key]['Target Count']['value'];
//            }
//
//            if ($goals_active_biometrics[$key]['target'] > $goals_active_biometrics[$key]['starting']) {
//                $progression = 'Raise ';
//            } else if (
//                $goals_active_biometrics[$key]['target'] <= $goals_active_biometrics[$key]['starting']
//            ) {
//                $progression = 'Lower ';
//            }
//
//
//            $goals['Data'][] = ['name' => '<B>' . $progression . $key . ' to ' . $goals_active_biometrics[$key]['target'] . $this->biometricsUnitMapping($key) . ' </B> from  ' . $goals_active_biometrics[$key]['starting'] . $this->biometricsUnitMapping($key)];
//
//        }
//        //=======================================
//        //======MONITORING MEDICATIONS===========
//        //=======================================
//
//        $monMedications['Section'] = 'Medications to Monitor';
//        if ($this->getItemsForParent('Medications to Monitor', $user) != false) {
//            $monMedications['Data'] = $this->getItemsForParent('Medications to Monitor', $user);
//        } else {
//            $none['name'] = 'None';
//            $monMedications['Data'][] = $none;
//        }
//
//        //=======================================
//        //========TAKING MEDICATIONS=============
//        //=======================================
//
//        $takMedications['Section'] = 'Medication Details';
//
//        $additional_information_item = CPRulesPCP::where('prov_id', '=', $user->program_id)->where('status', '=', 'Active')->where('section_text', 'Additional Information')->first();
//        $medication_information_item = CPRulesItem::where('pcp_id', $additional_information_item->pcp_id)->where('items_parent', 0)->where('items_text', 'Medications List')->first();
//        $medication_tracking_item = CPRulesItem::where('items_parent', $medication_information_item->items_id)->first();
//        $medications_taking = CPRulesUCP::where('items_id', $medication_tracking_item->items_id)->where('user_id', $user->id)->first();
//
//
//        if ($medications_taking->meta_value != null) {
//            $takMedications['Data'][] = ['name' => $medications_taking->meta_value];
//        } else {
//            $none = ['name' => 'None'];
//            $takMedications['Data'][] = $none;
//        }
//
//
//        //=======================================
//        //========SYMPTOMS TO MONITOR============
//        //=======================================
//
//        $symptoms['Section'] = 'Watch out for';
//        if ($this->getItemsForParent('Symptoms to Monitor', $user) != false) {
//            $symptoms['Data'] = $this->getItemsForParent('Symptoms to Monitor', $user);
//        } else {
//            $symptoms['Data'] = ['name' => 'None'];
//        }
//
//
//        //=======================================
//        //========LIFESTYLE TO MONITOR===========
//        //=======================================
//
//        $lifestyle['Section'] = 'Informing You About';
//
//        if ($this->getItemsForParent('Lifestyle to Monitor', $user) != false) {
//            $lifestyle['Data'] = $this->getItemsForParent('Lifestyle to Monitor', $user);
//        } else {
//            $lifestyle['Data'] = ['name' => 'None'];
//        }
//
//
//        //=======================================
//        //===========CHECK IN PLAN===============
//        //=======================================
//
//        $userConfig = $user->userConfig();
//        $check['Section'] = 'Check In Plan';
//        $check['Description'] = 'We will check in with you at' . $userConfig['study_phone_number'] . ' every day at ' . $userConfig['preferred_contact_time'];
//
//        $days = array('Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat');
//
//        for ($i = 0; $i < count($days); $i++) {
//            $check['Data'][] = ['day' => $days[$i], 'time' => $userConfig['preferred_contact_time']];
//        }
//
//
//        //=======================================
//        //===========OTHER INFO===============
//        //=======================================
//
//        $other['Section'] = 'Other Information';
//
//        $other['Data'] = array();
//        $pcp = CPRulesPCP::where('prov_id', '=', $user->program_id)->where('status', '=', 'Active')->where('section_text', 'Additional Information')->first();
//        //Get all the items for each section
//        $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->pluck('items_id')->all();
//
//        for ($i = 0; $i < count($items); $i++) {
//            //get id's of all lifestyle items that are active for the given user
//            $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->id)->first();
//            $items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
//            $items_detail_ucp[$i] = CPRulesUCP::where('items_id', $items_detail[$i]->items_id)->where('user_id', $user->id)->first();
//            if ($item_for_user[$i] != null) {
//                $count = 0;
//                //Find the items_text for the one's that are active
//                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
//                $other['Data'][] = ['name' => $user_items->items_text, 'comment' => ($items_detail_ucp[$i]->meta_value == '' ? 'Nothing' : $items_detail_ucp[$i]->meta_value)];
//            }
//        }
//
//        if (count($other['Data']) < 1) {
//            $other['Data'] = ['name' => 'None'];
//        }
//
//
//        //ADD ALL TO MAIN ARRAY
//
//        $careplan['CarePlan_Report'][] = $this->reportHeader($id);
//        $careplan['CarePlan_Report'][] = $treating;
//        $careplan['CarePlan_Report'][] = $goals;
//        $careplan['CarePlan_Report'][] = $monMedications;
//        $careplan['CarePlan_Report'][] = $takMedications;
//        $careplan['CarePlan_Report'][] = $symptoms;
//        $careplan['CarePlan_Report'][] = $check;
//        $careplan['CarePlan_Report'][] = $other;
//
//        return $careplan;
//    }

    //Generates View Data for Careplans
    // If only one element is passed, it returns just one array, otherwise it gives an assoc array

    public function makePdfCareplan($user)
    {
        $careplan = $this->carePlanGenerator([$user]);

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadView('wpUsers.patient.careplan.print', [
            'patient'             => $user,
            'problems'            => $careplan[$user->id]['problems'],
            'biometrics'          => $careplan[$user->id]['bio_data'],
            'symptoms'            => $careplan[$user->id]['symptoms'],
            'lifestyle'           => $careplan[$user->id]['lifestyle'],
            'medications_monitor' => $careplan[$user->id]['medications'],
            'taking_medications'  => $careplan[$user->id]['taking_meds'],
            'allergies'           => $careplan[$user->id]['allergies'],
            'social'              => $careplan[$user->id]['social'],
            'appointments'        => $careplan[$user->id]['appointments'],
            'other'               => $careplan[$user->id]['other'],
            'isPdf'               => true,
        ]);

        $file_name = base_path('storage/pdfs/careplans/' . str_random(40) . '.pdf');
        $pdf->save($file_name, true);

        return $file_name;
    }

    public function carePlanGenerator($patients)
    {
        $careplanReport = [];

        foreach ($patients as $user) {
            if (!is_object($user)) {
                $user = User::find($user);
            }
            $careplanReport[$user->id]['symptoms'] = $user->cpmSymptoms()->get()->pluck('name')->all();
            $careplanReport[$user->id]['problem'] = $user->cpmProblems()->get()->pluck('name')->all();
            $careplanReport[$user->id]['problems'] = (new \App\Services\CPM\CpmProblemService())->getProblemsWithInstructionsForUser($user);
            $careplanReport[$user->id]['lifestyle'] = $user->cpmLifestyles()->get()->pluck('name')->all();
            $careplanReport[$user->id]['biometrics'] = $user->cpmBiometrics()->get()->pluck('name')->all();
            $careplanReport[$user->id]['medications'] = $user->cpmMedicationGroups()->get()->pluck('name')->all();
        }

        $other_problems = $this->getInstructionsforOtherProblems($user);

        if (!empty($other_problems)) {
            $careplanReport[$user->id]['problems']['Other Problems'] = $other_problems;
        }

        //Get Biometrics with Values
        $careplanReport[$user->id]['bio_data'] = [];

        //Ignore Smoking - Untracked Biometric
        if (($key = array_search(CpmBiometric::SMOKING, $careplanReport[$user->id]['biometrics'])) !== false) {
            unset($careplanReport[$user->id]['biometrics'][$key]);
        }

        foreach ($careplanReport[$user->id]['biometrics'] as $metric) {

            $biometric = $user->cpmBiometrics->where('name', $metric)->first();
            $biometric_values = app(config('cpmmodelsmap.biometrics')[$biometric->type])->getUserValues($user);

            if ($biometric_values) {

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
            if ($metric == 'Blood Pressure') {

                if ($biometric_values['starting'] == 'N/A') {

                    $biometric_values['verb'] = 'Regulate';

                } else {

                    $biometric_values['verb'] = 'Maintain';
                }

            } else {
                if ($metric == 'Weight') {

                    $biometric_values['verb'] = 'Maintain';

                } else {

                    $biometric_values['verb'] = 'Raise';

                    if ($biometric_values['starting'] != "N/A") {

                        $starting = explode('/', $biometric_values['starting']);
                        $starting = $starting[0];
                        $target = explode('/', $biometric_values['target']);
                        $target = $target[0];

                        if ($starting > $target) {
                            $biometric_values['verb'] = 'Lower';

                        }
                    };
                }
            }

            $careplanReport[$user->id]['bio_data'][$metric]['target'] = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['verb'] = $biometric_values['verb'];

        }


        //Medications List
        if ($user->cpmMiscs->where('name', CpmMisc::MEDICATION_LIST)->first()) {
            $careplanReport[$user->id]['taking_meds'] = (new \App\Services\CPM\CpmMiscService())->getMiscWithInstructionsForUser($user,
                CpmMisc::MEDICATION_LIST);
        } else {
            $careplanReport[$user->id]['taking_meds'] = '';
        }

        //Allergies
        if ($user->cpmMiscs->where('name', CpmMisc::MEDICATION_LIST)->first()) {
            $careplanReport[$user->id]['allergies'] = (new \App\Services\CPM\CpmMiscService())->getMiscWithInstructionsForUser($user,
                CpmMisc::ALLERGIES);
        } else {
            $careplanReport[$user->id]['allergies'] = '';
        }

        //Social Services
        if ($user->cpmMiscs->where('name', CpmMisc::SOCIAL_SERVICES)->first()) {
            $careplanReport[$user->id]['social'] = (new \App\Services\CPM\CpmMiscService())->getMiscWithInstructionsForUser($user,
                CpmMisc::SOCIAL_SERVICES);
        } else {
            $careplanReport[$user->id]['social'] = '';
        }

        //Other
        if ($user->cpmMiscs->where('name', CpmMisc::OTHER)->first()) {
            $careplanReport[$user->id]['other'] = (new \App\Services\CPM\CpmMiscService())->getMiscWithInstructionsForUser($user,
                CpmMisc::OTHER);
        } else {
            $careplanReport[$user->id]['other'] = '';
        }

        //Appointments
        if ($user->cpmMiscs->where('name', CpmMisc::APPOINTMENTS)->first()) {
            $careplanReport[$user->id]['appointments'] = (new \App\Services\CPM\CpmMiscService())->getMiscWithInstructionsForUser($user,
                CpmMisc::APPOINTMENTS);
        } else {
            $careplanReport[$user->id]['appointments'] = '';
        }

        return $careplanReport;
    }

    public function getInstructionsForOtherProblems(User $user)
    {

        if (!$user) {
            //nullify
            return "User not found...";
        }

        // Other Conditions / CcdProblem List
        $ccdProblems = 'No instructions at this time';
        $problem = $user->cpmMiscs->where('name', CpmMisc::OTHER_CONDITIONS)->all();
        if (!empty($problem)) {
            $problems = CcdProblem::where('patient_id', '=', $user->id)->orderBy('name')->get();
            if ($problems->count() > 0) {
                $ccdProblems = '';
                $i = 0;
                foreach ($problems as $problem) {
                    if (empty($problem->name)) {
                        continue 1;
                    }
                    if ($i > 0) {
                        $ccdProblems .= '<br>';
                    }
                    $ccdProblems .= $problem->name;
                    $i++;
                }
            }
        }

        return $ccdProblems;

        /*
        $problem = $user->cpmMiscs->where('name',CpmMisc::OTHER_CONDITIONS)->all();

        if(empty($problem)){
            //https://youtu.be/LloIp0HMJjc?t=19s
            return '';
        }

        $instructions = CpmInstruction::find($problem[0]->pivot->cpm_instruction_id);

        if(empty($instructions)){
            //defualt
                return 'No instructions at this time';
        }

        return $instructions->name;
        */

    }

    public function createNotePdfReport(
        User $patient,
        User $sender,
        Note $note,
        array $careteam
    ) {
        foreach ($careteam as $providerId) {

            $provider = User::find($providerId);

            if (!$provider) {
                return false;
            }

            $locationId = $patient->getpreferredContactLocationAttribute();

            if (empty($locationId)) {
                return false;
            }

            //get foreign provider id
            $foreign_id = ForeignId::where('user_id', $providerId)->where('system', ForeignId::APRIMA)->first();

            if (empty($foreign_id)) {
                \Log::error("Provider $providerId has no Aprima Foreign id.");

                return false;
            }

            //update the foreign id to include a location as well
            if (empty($foreign_id->location_id)) {
                $foreign_id->location_id = $locationId;
                $foreign_id->save();
            }

            $file_name = $this->makePdfNote($patient, $sender, $note, $provider);

            $base_64_report = base64_encode(file_get_contents($file_name));

            $patientReport = PatientReports::create([
                'patient_id'  => $patient->id,
                'patient_mrn' => $patient->getMRNAttribute(),
                'provider_id' => $foreign_id->foreign_id,
                'file_type'   => PatientReports::NOTE,
                'file_base64' => $base_64_report,
                'location_id' => $locationId,
            ]);
        }
    }

    public function makePdfNote(
        User $patient,
        User $sender,
        Note $note,
        User $provider
    ) {
        $problems = $patient->cpmProblems()->get()->pluck('name')->all();

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadView('pdfs.note', [
            'patient'  => $patient,
            'problems' => $problems,
            'sender'   => $sender,
            'note'     => $note,
            'provider' => $provider,
        ]);

        $file_name = base_path('storage/pdfs/notes/' . str_random(40) . '.pdf');
        $pdf->save($file_name, true);

        return $file_name;
    }


}