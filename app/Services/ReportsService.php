<?php namespace App\Services;

use App\CarePlan;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmMisc;
use App\Services\CPM\CpmMiscService;
use App\Services\CPM\CpmProblemService;
use App\User;
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

    public function getBiometricsToMonitor(User $user)
    {
        return $user->cpmBiometrics()->get()->pluck('name')->all();
    }

    public function getProblemsToMonitor(User $user)
    {
        if ( ! $user) {
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
        $medication_obs = DB::table('rules_questions')
                            ->select(
                                'lv_observations.id',
                                'rules_items.items_text',
                                'lv_observations.obs_date',
                                'lv_observations.obs_value',
                                'lv_observations.obs_key',
                                'lv_observations.obs_message_id'
                            )
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
            $temp_meds[$cat]['yes']   = 0;
        }

        //Add scaffolding to sections
        if ($fromApp) {
            $meds_array['Better']['description']     = '';
            $meds_array['Needs Work']['description'] = '';
            $meds_array['Worse']['description']      = '';
        } else {
            $meds_array['Better']['description']     = [];
            $meds_array['Needs Work']['description'] = [];
            $meds_array['Worse']['description']      = [];
        }

        foreach ($medication_obs as $obs) {
            $yes   = 0;
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
            $yes   = $value['yes'];
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
        $bio              = CpmBiometric::whereName(str_replace('_', ' ', $biometric))->first();
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
                $color       = 'red';
                $progression = 'up';
                $copy        = 'Worse';
            } else {
                if ($change < 0) { //The weekly average has decreased
                    $color       = 'green';
                    $progression = 'down';
                    $copy        = 'Better';
                } else { //The weekly average is unchanged
                    $color       = 'yellow';
                    $copy        = 'Unchanged';
                    $progression = '';
                }
            }
        } else {
            if ($weeklyReading1 <= 130 && $weeklyReading1 > 70) {
                $color = "green";
                $copy  = "Good";
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
                    $copy  = "Low";
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
                        $copy  = "Too Low";
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

        $changes_array                = [];
        $changes_array['change']      = $change;
        $changes_array['unit']        = $this->biometricsUnitMapping("Blood Sugar");
        $changes_array['color']       = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status']      = $copy;

        return $changes_array;
    }

    public function analyzeBloodPressure(
        $weeklyReading1,
        $weeklyReading2
    ) {
        $change = $weeklyReading1 - $weeklyReading2;

        if ($weeklyReading1 > 130) {
            if ($change > 0) { //The weekly average has increased
                $color       = 'red';
                $progression = 'up';
                $copy        = 'Worse';
            } else {
                if ($change < 0) {
                    $color       = 'green';
                    $progression = 'down';
                    $copy        = 'Better';
                } else {
                    $color       = 'yellow';
                    $copy        = 'Unchanged';
                    $progression = '';
                }
            }
        } else {
            if ($weeklyReading1 <= 130 && $weeklyReading1 > 100) {
                $color = "green";
                $copy  = "Good";
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
                    $copy  = "Low";
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

        $changes_array                = [];
        $changes_array['change']      = $change;
        $changes_array['unit']        = $this->biometricsUnitMapping("Blood Pressure");
        $changes_array['color']       = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status']      = $copy;

        return $changes_array;
    }

    public function analyzeWeight(
        $weeklyReading1,
        $weeklyReading2
    ) {
        $change = $weeklyReading1 - $weeklyReading2;
        if ($change > 0) {
            $color       = 'grey';
            $progression = 'up';
            $copy        = 'Increased';
        } else {
            if ($change < 0) {
                $color       = 'grey';
                $progression = 'down';
                $copy        = 'Decreased';
            } else {
                $color       = 'grey';
                $copy        = 'Unchanged';
                $progression = '';
            }
        }
        $changes_array                = [];
        $changes_array['change']      = $change;
        $changes_array['unit']        = $this->biometricsUnitMapping("Weight");
        $changes_array['color']       = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status']      = $copy;

        return $changes_array;
    }

    public function carePlanGenerator($patients)
    {
        $careplanReport = [];

        foreach ($patients as $user) {
            if ( ! is_object($user)) {
                $user = User::find($user);
            }
            $careplanReport[$user->id]['symptoms']    = $user->cpmSymptoms()->get()->pluck('name')->all();
            $careplanReport[$user->id]['problem']     = $user->cpmProblems()->get()->pluck('name')->all();
            $careplanReport[$user->id]['problems']    = app(CpmProblemService::class)->getProblemsWithInstructionsForUser($user);
            $careplanReport[$user->id]['lifestyle']   = $user->cpmLifestyles()->get()->pluck('name')->all();
            $careplanReport[$user->id]['biometrics']  = $user->cpmBiometrics()->get()->pluck('name')->all();
            $careplanReport[$user->id]['medications'] = $user->cpmMedicationGroups()->get()->pluck('name')->all();
        }

        $other_problems = $this->getInstructionsforOtherProblems($user);

        if ( ! empty($other_problems)) {
            $careplanReport[$user->id]['problems']['Other Problems'] = $other_problems;
        }

        //Get Biometrics with Values
        $careplanReport[$user->id]['bio_data'] = [];

        //Ignore Smoking - Untracked Biometric
        if (($key = array_search(CpmBiometric::SMOKING, $careplanReport[$user->id]['biometrics'])) !== false) {
            unset($careplanReport[$user->id]['biometrics'][$key]);
        }

        foreach ($careplanReport[$user->id]['biometrics'] as $metric) {
            $biometric        = $user->cpmBiometrics->where('name', $metric)->first();
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
                $biometric_values['target']   = 'TBD';
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
                        $target   = explode('/', $biometric_values['target']);
                        $target   = $target[0];

                        if ($starting > $target) {
                            $biometric_values['verb'] = 'Lower';
                        }
                    };
                }
            }

            $careplanReport[$user->id]['bio_data'][$metric]['target']   = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['verb']     = $biometric_values['verb'];
        }


        //Medications List
        if ($user->cpmMiscs->where('name', CpmMisc::MEDICATION_LIST)->first()) {
            $careplanReport[$user->id]['taking_meds'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::MEDICATION_LIST
            );
        } else {
            $careplanReport[$user->id]['taking_meds'] = '';
        }

        //Allergies
        if ($user->cpmMiscs->where('name', CpmMisc::MEDICATION_LIST)->first()) {
            $careplanReport[$user->id]['allergies'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::ALLERGIES
            );
        } else {
            $careplanReport[$user->id]['allergies'] = '';
        }

        //Social Services
        if ($user->cpmMiscs->where('name', CpmMisc::SOCIAL_SERVICES)->first()) {
            $careplanReport[$user->id]['social'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::SOCIAL_SERVICES
            );
        } else {
            $careplanReport[$user->id]['social'] = '';
        }

        //Other
        if ($user->cpmMiscs->where('name', CpmMisc::OTHER)->first()) {
            $careplanReport[$user->id]['other'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::OTHER
            );
        } else {
            $careplanReport[$user->id]['other'] = '';
        }

        //Appointments
        if ($user->cpmMiscs->where('name', CpmMisc::APPOINTMENTS)->first()) {
            $careplanReport[$user->id]['appointments'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::APPOINTMENTS
            );
        } else {
            $careplanReport[$user->id]['appointments'] = '';
        }

        return $careplanReport;
    }

    public function getInstructionsForOtherProblems(User $user)
    {

        if ( ! $user) {
            //nullify
            return "User not found...";
        }

        // Other Conditions / Problem List
        $ccdProblems = 'No instructions at this time';
        $problem     = $user->cpmMiscs->where('name', CpmMisc::OTHER_CONDITIONS)->all();
        if ( ! empty($problem)) {
            $problems = Problem::where('patient_id', '=', $user->id)->orderBy('name')->get();
            if ($problems->count() > 0) {
                $ccdProblems = '';
                $i           = 0;
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
}
