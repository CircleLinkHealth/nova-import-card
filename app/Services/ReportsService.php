<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use Illuminate\Support\Facades\DB;

class ReportsService
{
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
                $color = 'green';
                $copy  = 'Good';
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
                    $color = 'yellow';
                    $copy  = 'Low';
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
        $changes_array['unit']        = $this->biometricsUnitMapping('Blood Pressure');
        $changes_array['color']       = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status']      = $copy;

        return $changes_array;
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
                $color = 'green';
                $copy  = 'Good';
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
                    $color = 'yellow';
                    $copy  = 'Low';
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
                        $color = 'red';
                        $copy  = 'Too Low';
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
        $changes_array['unit']        = $this->biometricsUnitMapping('Blood Sugar');
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
        $changes_array['unit']        = $this->biometricsUnitMapping('Weight');
        $changes_array['color']       = $color;
        $changes_array['progression'] = $progression;
        $changes_array['status']      = $copy;

        return $changes_array;
    }

    public function biometricsIndicators(
        $weeklyReading1,
        $weeklyReading2,
        $biometric,
        $target
    ) {//debug($biometric);
        if ('Blood Sugar' == $biometric) {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
            return $this->analyzeBloodSugar($weeklyReading1, $weeklyReading2);
        }
        if ('Blood Pressure' == $biometric) {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
            return $this->analyzeBloodPressure($weeklyReading1, $weeklyReading2);
        }
        if ('Weight' == $biometric) {
//            debug($this->analyzeBloodSugar($weeklyReading1, $weeklyReading2));
            return $this->analyzeWeight($weeklyReading1, $weeklyReading2);
        }
    }

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

    public function getBiometricsToMonitor(User $user)
    {
        return $user->cpmBiometrics()->get()->pluck('name')->all();
    }

    public function getInstructionsForOtherProblems(User $user)
    {
        $user->loadMissing('ccdProblems');

        $ccdProblems = '';
        $i           = 0;
        foreach ($user->ccdProblems as $problem) {
            if (empty($problem->name)) {
                continue 1;
            }
            if ($i > 0) {
                $ccdProblems .= '<br>';
            }
            $ccdProblems .= $problem->name;
            ++$i;
        }

        return $ccdProblems;
    }

    public function getLifestyleToMonitor(CarePlan $carePlan)
    {
        $temp = [];
        if ($carePlan) {
            foreach ($carePlan->careSections as $section) {
                if ('lifestyle-to-monitor' == $section->name) {
                    foreach ($section->carePlanItems as $item) {
                        if ('Active' == $item->meta_value) {
                            $temp[] = $item->careItem->display_name;
                        }
                    }
                }
            }
        }

        return $temp;
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
                ++$temp_meds[$obs->items_text]['total'];
                if ('Y' == $obs->obs_value) {
                    ++$temp_meds[$obs->items_text]['yes'];
                }
            }
        }

        //Remove meds with no observations
        foreach ($temp_meds as $key => $value) {
            if (0 == $value['total']) {
                unset($temp_meds[$key]);
            }
        }

        foreach ($temp_meds as $key => $value) {
            $yes   = $value['yes'];
            $total = $value['total'];

            if (0 != $yes && 0 != $total) {
                $adhereance_percent = doubleval($yes / $total);
            } else {
                if (0 == $yes && 1 == $total) {
                    $adhereance_percent = 0;
                } else {
                    if (0 == $yes && 0 == $total) {
                        $adhereance_percent = 1;
                    } else {
                        if (0 == $yes) {
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
                case $adhereance_percent > 0.8:
                    $meds_array['Better']['description'][] = $key;
                    break;
                case $adhereance_percent >= 0.5:
                    $meds_array['Needs Work']['description'][] = $key;
                    break;
                case $adhereance_percent < 0.5:
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

    public function getSymptomsToMonitor(CarePlan $carePlan)
    {
        $temp = [];
        if ($carePlan) {
            foreach ($carePlan->careSections as $section) {
                if ('symptoms-to-monitor' == $section->name) {
                    foreach ($section->carePlanItems as $item) {
                        if ('Active' == $item->meta_value) {
                            $temp[] = $item->careItem->display_name;
                        }
                    }
                }
            }
        }

        return $temp;
    }

    public function getTargetValueForBiometric(
        $biometric,
        User $user,
        $showUnits = true
    ) {
        $bio              = CpmBiometric::whereName(str_replace('_', ' ', $biometric))->first();
        $biometric_values = app(config('cpmmodelsmap.biometrics')[$bio->type])->getUserValues($user);

        if ($showUnits) {
            return $biometric_values['target'].ReportsService::biometricsUnitMapping($biometric);
        }

        return $biometric_values['target'];
    }

    public function medicationsList(User $user)
    {
        return $user->cpmMedicationGroups()->get()->pluck('name')->all();
    }
}
