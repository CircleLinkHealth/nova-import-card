<?php namespace App\Http\Controllers;

use App\Activity;
use App\CareItemUserValue;
use App\CareItem;
use App\CarePlan;
use App\CarePlanItem;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Observation;
use App\Services\CareplanService;
use App\Services\ReportsService;
use App\User;
use App\WpBlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\CPM\CpmProblem;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ReportsController extends Controller
{

    //PROGRESS REPORT
    public function index(Request $request, $patientId = false)
    {

        $user = User::find($patientId);
        $carePlan = CarePlan::where('id', '=', $user->care_plan_id)
            ->first();
        if ($carePlan) {
            $carePlan->build($user->ID);
        }
        $treating = (new ReportsService())->getProblemsToMonitor($carePlan);

        $biometrics = ['Blood_Sugar', 'Blood_Pressure', 'Weight'];
        $biometrics_data = array();
        $biometrics_array = array();

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = (new ReportsService())->getBiometricsData($biometric, $user);
        }//debug($biometrics_data);

        foreach ($biometrics_data as $key => $value) {
            $bio_name = $key;
            if ($value != null) {
                $first = reset($value);
                $last = end($value);
                $changes = (new ReportsService())->biometricsIndicators(intval($last->Avg), intval($first->Avg), $bio_name, (new ReportsService())->getTargetValueForBiometric($carePlan, $bio_name, $user));
                //debug($changes);
                $biometrics_array[$bio_name]['change'] = $changes['change'];
                $biometrics_array[$bio_name]['progression'] = $changes['progression'];
                $biometrics_array[$bio_name]['status'] = (isset($changes['status'])) ? $changes['status'] : 'Unchanged';
//				//$changes['bio']= $bio_name;debug($changes);
                $biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);
            }

            $count = 1;
            $biometrics_array[$bio_name]['data'] = '';
            $biometrics_array[$bio_name]['max'] = -1;
            //$first = reset($array);
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['unit'] = (new ReportsService())->biometricsUnitMapping(str_replace('_', ' ', $bio_name));
                    $biometrics_array[$bio_name]['target'] = (new ReportsService())->getTargetValueForBiometric($carePlan, $bio_name, $user);
                    $biometrics_array[$bio_name]['reading'] = intval($value->Avg);
                    if (intval($value->Avg) > $biometrics_array[$bio_name]['max']) {
                        $biometrics_array[$bio_name]['max'] = intval($value->Avg);
                    }
                    $biometrics_array[$bio_name]['data'] .= '{ id:' . $count . ', Week:\'' . $value->day . '\', Reading:' . intval($value->Avg) . '} ,';
                    $count++;
                }
            } else {
                //no data
                unset($biometrics_array[$bio_name]);
            }
        }//dd($biometrics_array);

        // get provider
        $provider = User::find($user->leadContactID);

        //Medication Tracking:
        $medications = (new ReportsService())->getMedicationStatus($user, $carePlan, false);

        $data = [
            'treating' => $treating,
            'patientId' => $patientId,
            'patient' => $user,
            'provider' => $provider,
            'medications' => $medications,
            'tracking_biometrics' => $biometrics_array
        ];

        return view('wpUsers.patient.progress', $data);

    }

    public function u20(Request $request, $patientId = false)
    {

        $input = $request->all();

        if (isset($input['selectMonth'])) {
            $time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $month_selected = $time->format('m');
            $month_selected_text = $time->format('F');
            $year_selected = $time->format('Y');
            $start = $time->startOfMonth()->toDateTimeString();
            $end = $time->endOfMonth()->toDateTimeString();
        } else {
            $time = Carbon::now();
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');
            $month_selected_text = $time->format('F');
            $start = Carbon::now()->startOfMonth()->toDateTimeString();
            $end = Carbon::now()->endOfMonth()->toDateTimeString();
        }

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

        $u20_patients = array();

        $act_count = 0;
        foreach ($patients as $patient) {
            $monthly_time = intval($patient->MonthlyTime);
            $program = WpBlog::find($patient->program_id);
            if ($program) $programName = $program->display_name;

            if ($patient->hasRole('participant')) {
                $u20_patients[$act_count]['site'] = $programName;

                $u20_patients[$act_count]['colsum_careplan'] = 0;
                $u20_patients[$act_count]['colsum_changes'] = 0;
                $u20_patients[$act_count]['colsum_progress'] = 0;
                $u20_patients[$act_count]['colsum_rpm'] = 0;
                $u20_patients[$act_count]['colsum_tcc'] = 0;
                $u20_patients[$act_count]['colsum_other'] = 0;
                $u20_patients[$act_count]['colsum_total'] = 0;
                $u20_patients[$act_count]['ccm_status'] = ucwords($patient->CCMStatus);
                $u20_patients[$act_count]['dob'] = Carbon::parse($patient->birthDate)->format('m/d/Y');
                $u20_patients[$act_count]['patient_name'] = $patient->fullName;
                $u20_patients[$act_count]['patient_id'] = $patient->ID;
                $acts = DB::table('lv_activities')
                    ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration) as duration'))
                    ->where('patient_id', $patient->ID)
                    ->whereBetween('performed_at', [
                        $start, $end
                    ])
                    ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                    ->orderBy('performed_at', 'desc')
                    ->get();

//				foreach ($acts as $key => $value) {
//					$acts[$key]['patient'] = User::find($patient->ID);
//				}

                foreach ($acts as $activity) {
                    if (in_array($activity->type, Activity::rollup_category_care_plan())) {
                        $u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
                    } else if (in_array($activity->type, Activity::rollup_category_progress())) {
                        $u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
                    } else if (in_array($activity->type, Activity::rollup_category_rpm())) {
                        $u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
                    } else if (in_array($activity->type, Activity::rollup_category_tcm())) {
                        $u20_patients[$act_count]['colsum_tcc'] += intval($activity->duration);
                    } else {
                        $u20_patients[$act_count]['colsum_other'] += intval($activity->duration);
                    }
                    $u20_patients[$act_count]['colsum_total'] += intval($activity->duration);

                }
                $act_count++;
            }
        }
        debug($u20_patients);
        foreach ($u20_patients as $key => $value) {
            if ($value['colsum_total'] >= 1200) {
                unset($u20_patients[$key]);
            }
        }

        $reportData = "data:" . json_encode(array_values($u20_patients)) . "";
        debug(json_encode($u20_patients));

        $years = array();
        for ($i = 0; $i < 3; $i++) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $act_data = true;
        if ($u20_patients == null) {
            $act_data = false;
        }

        $data = [
            'activity_json' => $reportData,
            'years' => array_reverse($years),
            'month_selected' => $month_selected,
            'month_selected_text' => $month_selected_text,
            'year_selected' => $year_selected,
            'months' => $months,
            //'patient' => $patient_,
            'data' => $act_data
        ];
        //debug($reportData);

        return view('reports.u20', $data);
    }

    public function billing(Request $request, $patientId = false)
    {
        //$patient_ = User::find($patientId);
        $input = $request->all();

        if (isset($input['selectMonth'])) {
            $time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $start = $time->startOfMonth()->toDateTimeString();
            $end = $time->endOfMonth()->toDateTimeString();
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');
        } else {
            $time = Carbon::now();
            $start = Carbon::now()->startOfMonth()->toDateTimeString();
            $end = Carbon::now()->endOfMonth()->toDateTimeString();
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');
        }

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

        $u20_patients = array();
        $act_count = 0;

        foreach ($patients as $patient) {
            $monthly_time = intval($patient->MonthlyTime);
            $program = WpBlog::find($patient->program_id);
            if ($program) $programName = $program->display_name;
            if ($patient->hasRole('participant')) {
                $u20_patients[$act_count]['site'] = $programName;
                $u20_patients[$act_count]['colsum_careplan'] = 0;
                $u20_patients[$act_count]['colsum_changes'] = 0;
                $u20_patients[$act_count]['colsum_progress'] = 0;
                $u20_patients[$act_count]['colsum_rpm'] = 0;
                $u20_patients[$act_count]['colsum_tcc'] = 0;
                $u20_patients[$act_count]['colsum_other'] = 0;
                $u20_patients[$act_count]['colsum_total'] = 0;
                $u20_patients[$act_count]['ccm_status'] = ucwords($patient->CCMStatus);
                $u20_patients[$act_count]['dob'] = Carbon::parse($patient->birthDate)->format('m/d/Y');
                $u20_patients[$act_count]['patient_name'] = $patient->fullName;
                $provider = User::find(intval($patient->getBillingProviderIDAttribute()));
                if ($provider) {
                    $u20_patients[$act_count]['provider_name'] = $provider->fullName;
                } else {
                    $u20_patients[$act_count]['provider_name'] = '';
                }
                $u20_patients[$act_count]['patient_id'] = $patient->ID;
                $acts = DB::table('lv_activities')
                    ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration) as duration'))
                    ->where('patient_id', $patient->ID)
                    ->whereBetween('performed_at', [
                        $start, $end
                    ])
                    ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                    ->orderBy('performed_at', 'desc')
                    ->get();

                foreach ($acts as $activity) {
                    if($activity) {

                        if (in_array($activity->type, Activity::rollup_category_care_plan())) {
                            $u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
                        } else if (in_array($activity->type, Activity::rollup_category_progress())) {
                            $u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
                        } else if (in_array($activity->type, Activity::rollup_category_rpm())) {
                            $u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
                        } else if (in_array($activity->type, Activity::rollup_category_tcm())) {
                            $u20_patients[$act_count]['colsum_tcc'] += intval($activity->duration);
                        } else {
                            $u20_patients[$act_count]['colsum_other'] += intval($activity->duration);
                        }
                        $u20_patients[$act_count]['colsum_total'] += intval($activity->duration);
                    }
                } $act_count++;
            }
        }

        foreach ($u20_patients as $key => $value) {
            if ($value['colsum_total'] < 1200) {
                unset($u20_patients[$key]);
            }
        }


        $reportData = "data:" . json_encode(array_values($u20_patients)) . "";

        $years = array();
        for ($i = 0; $i < 3; $i++) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

        $act_data = true;
        if ($u20_patients == null) {
            $act_data = false;
        }

        $data = [
            'activity_json' => $reportData,
            'years' => array_reverse($years),
            'month_selected' => $month_selected,
            'year_selected' => $year_selected,
            'month_selected_text' => $month_selected_text,
            'months' => $months,
            'data' => $act_data
        ];
        //debug($reportData);

        return view('reports.billing', $data);
    }

    public function progress(Request $request, $id = false)
    {
        if ($request->header('Client') == 'mobi') {
            // get and validate current user
            \JWTAuth::setIdentifier('ID');
            $wpUser = \JWTAuth::parseToken()->authenticate();
            if (!$wpUser) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } else {
            // get user
            $wpUser = User::find($id);
            if (!$wpUser) {
                return response("User not found", 401);
            }
        }

        $progressReport = new ReportsService();
        $feed = $progressReport->progress($wpUser->ID);

        return json_encode($feed);
    }

    public function careplan(Request $request, $id = false)
    {
        if ($request->header('Client') == 'mobi') {
            // get and validate current user
            \JWTAuth::setIdentifier('ID');
            $wpUser = \JWTAuth::parseToken()->authenticate();
            if (!$wpUser) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } else {
            // get user
            $wpUser = User::find($id);
            if (!$wpUser) {
                return response("User not found", 401);
            }
        }

        $progressReport = new ReportsService();
        $feed = $progressReport->careplan($wpUser->ID);

        return response()->json($feed);
    }

    public function viewPrintCareplan(Request $request, $patientId = false)
    {
        if (!$patientId) {
            return "Patient Not Found..";
        }
        $reportService = new ReportsService();
        $careplan = $reportService->carePlanGenerator([$patientId]);

        return view('wpUsers.patient.careplan.print',
            [
                'patient' => User::find($patientId),
                'treating' => $careplan[$patientId]['treating'],
                'biometrics' => $careplan[$patientId]['bio_data'],
                'symptoms' => $careplan[$patientId]['symptoms'],
                'lifestyle' => $careplan[$patientId]['lifestyle'],
                'medications_monitor' => $careplan[$patientId]['medications'],
                'taking_medications' => $careplan[$patientId]['taking_meds'],
                'allergies' => $careplan[$patientId]['allergies'],
                'social' => $careplan[$patientId]['social'],
                'appointments' => $careplan[$patientId]['appointments'],
                'other' => $careplan[$patientId]['other']
            ]);

        return response("User not found", 401);
    }

    /**
     * @param Request $request
     * @param bool|false $patientId
     * @return \Illuminate\View\View
     */
    public function biometricsCharts(Request $request, $patientId = false)
    {

        $patient = User::find($patientId);

        $biometrics = ['Weight', 'Blood_Sugar', 'Blood_Pressure'];
        $biometrics_data = array();
        $biometrics_array = array();

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = (new ReportsService())->getBiometricsData($biometric, $patient);
        }            //debug($biometrics_data);

        foreach ($biometrics_data as $key => $value) {
            $bio_name = $key;
            $count = 1;
            $biometrics_array[$bio_name]['data'] = '';
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['data'] .= '{ id:' . $count . ', Week:\'' . $value->day . '\', Reading:' . intval($value->Avg) . '} ,';
                    $count++;
                }
            } else {
                //no data
                $biometrics_array[$bio_name]['data'] = '';
            }//debug($biometrics_array);
        }
        debug($biometrics_array);

        return view('wpUsers.patient.biometric-chart', ['patient' => $patient, 'biometrics_array' => $biometrics_array]);
    }


    public function excelReportT1()
    {
        // get all users with 1 condition
        $usersCondition = array();
        $problems = array();
        $cpmProblems = CpmProblem::all();
        if($cpmProblems->count() > 0) {
            foreach ($cpmProblems as $cpmProblem) {
                $problems[$cpmProblem->id] = $cpmProblem->name;
                $problemNames[] = $cpmProblem->name;
            }
        }
        $careItemNames = CareItem::whereIn('display_name', $problemNames)->lists('display_name', 'id');
        $careItems = CareItem::whereIn('display_name', $problemNames)->lists('id');
        $careItemUserValues = CareItemUserValue::whereIn('care_item_id', $careItems)->where('value', 'Active')->get();
        if($careItemUserValues->count() > 0) {
            foreach ($careItemUserValues as $careItemUserValue) {
                $usersCondition[$careItemUserValue->user_id] = $careItemNames[$careItemUserValue->care_item_id];
            }
        }
        $date = date('Y-m-d H:i:s');
        $users = User::all();

        Excel::create('CLH-Report-'.$date, function($excel) use($date, $users, $usersCondition) {

            // Set the title
            $excel->setTitle('CLH Report T1');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T1');

            // Our first sheet
            $excel->sheet('Sheet 1', function($sheet) use($users, $usersCondition) {
                $sheet->protect('clhpa$$word', function(\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow(array(
                    'id', 'name', 'condition', 'program'
                ));
                foreach($users as $user) {
                    if($i > 2000000) {
                        continue 1;
                    }

                    $condition = 'N/A';
                    if(isset($usersCondition[$user->ID])) {
                        $condition = $usersCondition[$user->ID];
                    }
                    $programName = 'N/A';
                    $program = WpBlog::find($user->program_id);
                    if($program) {
                        $programName = $program->display_name;
                    }
                    $sheet->appendRow(array(
                        $user->ID, $user->fullName, $condition, $programName
                    ));
                    $i++;
                }
            });

            /*
            // Our second sheet
            $excel->sheet('Second sheet', function($sheet) {

            });
            */
        })->export('xls');
    }

}
