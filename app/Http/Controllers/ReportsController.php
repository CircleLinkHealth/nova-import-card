<?php namespace App\Http\Controllers;

use App\Activity;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests;
use App\Http\Controllers\Controller;

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
                $changes = (new ReportsService())->biometricsIndicators(intval($last->Avg), intval($first->Avg), $bio_name, (new ReportsService())->getTargetValueForBiometric($bio_name, $user));
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
                    $biometrics_array[$bio_name]['target'] = (new ReportsService())->getTargetValueForBiometric($bio_name, $user);
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
        //$patient_ = User::find($patientId);
        $input = $request->all();

        if (isset($input['selectMonth'])) {
            $time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $month_selected = $time->format('m');
            $month_selected_text = $time->format('F');
            $year_selected = $time->format('Y');
            $start = $time->startOfMonth()->format('Y-m-d');
            $end = $time->endOfMonth()->format('Y-m-d');
        } else {
            $time = Carbon::now();
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');
            $month_selected_text = $time->format('F');
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

        $u20_patients = array();
        debug(jdmonthname(1, 1));
        debug($month_selected);

        // ROLLUP CATEGORIES
        $CarePlan = array('Edit/Modify Care Plan', 'Initial Care Plan Setup', 'Care Plan View/Print', 'Patient History Review', 'Patient Item Detail Review', 'Review Care Plan (offline)');
        $Progress = array('Review Patient Progress (offline)', 'Progress Report Review/Print');
        $RPM = array('Patient Alerts Review', 'Patient Overview Review', 'Biometrics Data Review', 'Lifestyle Data Review', 'Symptoms Data Review', 'Assessments Scores Review',
            'Medications Data Review', 'Input Observation');
        $TCM = array('Test (Scheduling, Communications, etc)', 'Transitional Care Management Activities', 'Call to Other Care Team Member', 'Appointments');
        $Other = array('other', 'Medication Reconciliation');
        $act_count = 0;
        foreach ($patients as $patient) {
            $monthly_time = intval($patient->getMonthlyTimeAttribute());
            if ($patient->hasRole('participant')) {
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
                    if (in_array($activity->type, $CarePlan)) {
                        $u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
                    } else if (in_array($activity->type, $Progress)) {
                        $u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
                    } else if (in_array($activity->type, $RPM)) {
                        $u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
                    } else if (in_array($activity->type, $TCM)) {
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
            $start = $time->startOfMonth()->format('Y-m-d');
            $end = $time->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');
        } else {
            $time = Carbon::now();
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');

        }

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

        $u20_patients = array();
        $billable_patients = array();

        // ROLLUP CATEGORIES
        $CarePlan = array('Edit/Modify Care Plan', 'Initial Care Plan Setup', 'Care Plan View/Print', 'Patient History Review', 'Patient Item Detail Review', 'Review Care Plan (offline)');
        $Progress = array('Review Patient Progress (offline)', 'Progress Report Review/Print');
        $RPM = array('Patient Alerts Review', 'Patient Overview Review', 'Biometrics Data Review', 'Lifestyle Data Review', 'Symptoms Data Review', 'Assessments Scores Review',
            'Medications Data Review', 'Input Observation');
        $TCM = array('Test (Scheduling, Communications, etc)', 'Transitional Care Management Activities', 'Call to Other Care Team Member', 'Appointments');
        $Other = array('other', 'Medication Reconciliation');
        $act_count = 0;

        foreach ($patients as $patient) {
            $monthly_time = intval($patient->getMonthlyTimeAttribute());
            if ($patient->hasRole('participant')) {
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

//				foreach ($acts as $key => $value) {
//					$acts[$key]['patient'] = User::find($patient->ID);
//				}

                foreach ($acts as $activity) {
                    //$u20_patients[$act_count]['provider'] = User::find($activity->provider_id)->getFullNameAttribute();
                    if (in_array($activity->type, $CarePlan)) {
                        $u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
                    } else if (in_array($activity->type, $Progress)) {
                        $u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
                    } else if (in_array($activity->type, $RPM)) {
                        $u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
                    } else if (in_array($activity->type, $TCM)) {
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
            if ($value['colsum_total'] < 1200) {
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
            'year_selected' => $year_selected,
            'month_selected_text' => $month_selected_text,
            'months' => $months,
            //'patient' => $patient_,
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
            //$first = reset($array);
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

}
