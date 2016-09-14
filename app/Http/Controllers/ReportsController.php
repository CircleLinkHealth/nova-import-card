<?php namespace App\Http\Controllers;

use App\CareItem;
use App\CareItemUserValue;
use App\Formatters\WebixFormatter;
use App\Location;
use App\Models\CPM\CpmProblem;
use App\PageTimer;
use App\Program;
use App\Services\CCD\CcdInsurancePolicyService;
use App\Services\CPM\CpmProblemService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    private $service;
    private $formatter;

    public function __construct(ReportsService $service, WebixFormatter $formatter)
    {
        $this->service = $service;
        $this->formatter = $formatter;
    }

    //PROGRESS REPORT
    public function index(Request $request, $patientId = false)
    {

        $user = User::find($patientId);
        $treating = (new CpmProblemService())->getDetails($user);
        $biometrics = $this->service->getBiometricsToMonitor($user);
        $biometrics_data = array();
        $biometrics_array = array();

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = $this->service->getBiometricsData(str_replace(' ', '_', $biometric), $user);
        }

        foreach ($biometrics_data as $key => $value) {
            $bio_name = $key;
            if ($value != null) {
                $first = reset($value);
                $last = end($value);
                $changes = $this->service->biometricsIndicators(intval($last->Avg), intval($first->Avg), $bio_name, (new ReportsService())->getTargetValueForBiometric($bio_name, $user));

                $biometrics_array[$bio_name]['change'] = $changes['change'];
                $biometrics_array[$bio_name]['progression'] = $changes['progression'];
                $biometrics_array[$bio_name]['status'] = (isset($changes['status'])) ? $changes['status'] : 'Unchanged';
                //$changes['bio']= $bio_name;debug($changes);
                $biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);
            }//debug($biometrics_array);

            $count = 1;
            $biometrics_array[$bio_name]['data'] = '';
            $biometrics_array[$bio_name]['max'] = -1;
            //$first = reset($array);
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['unit'] = $this->service->biometricsUnitMapping(str_replace('_', ' ', $bio_name));
                    $biometrics_array[$bio_name]['target'] = $this->service->getTargetValueForBiometric($bio_name, $user, false);
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
        $medications = $this->service->getMedicationStatus($user, false);

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

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
            ->with('primaryProgram')
            ->get();

        $u20_patients = array();

        // ROLLUP CATEGORIES
        $CarePlan = array('Edit/Modify Care Plan', 'Initial Care Plan Setup', 'Care Plan View/Print', 'Patient History Review', 'Patient Item Detail Review', 'Review Care Plan (offline)');
        $Progress = array('Review Patient Progress (offline)', 'Progress Report Review/Print');
        $RPM = array('Patient Alerts Review', 'Patient Overview Review', 'Biometrics Data Review', 'Lifestyle Data Review', 'Symptoms Data Review', 'Assessments Scores Review',
            'Medications Data Review', 'Input Observation');
        $TCM = array('Test (Scheduling, Communications, etc)', 'Transitional Care Management Activities', 'Call to Other Care Team Member', 'Appointments');
        $Other = array('other', 'Medication Reconciliation');

        $patient_counter = 0;
        foreach ($patients as $patient) {
            //$monthly_time = intval($patient->getMonthlyTimeAttribute());
            $program = $patient->primaryProgram;
            if ($program) $programName = $program->display_name;

            if ($patient->hasRole('participant')) {
                $u20_patients[$patient_counter]['site'] = $programName;

                $u20_patients[$patient_counter]['colsum_careplan'] = 0;
                $u20_patients[$patient_counter]['colsum_changes'] = 0;
                $u20_patients[$patient_counter]['colsum_progress'] = 0;
                $u20_patients[$patient_counter]['colsum_rpm'] = 0;
                $u20_patients[$patient_counter]['colsum_tcc'] = 0;
                $u20_patients[$patient_counter]['colsum_other'] = 0;
                $u20_patients[$patient_counter]['colsum_total'] = 0;
                $u20_patients[$patient_counter]['ccm_status'] = ucwords($patient->CCMStatus);
                $u20_patients[$patient_counter]['dob'] = Carbon::parse($patient->birthDate)->format('m/d/Y');
                $u20_patients[$patient_counter]['patient_name'] = $patient->fullName;
                $u20_patients[$patient_counter]['patient_id'] = $patient->ID;
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
                        $u20_patients[$patient_counter]['colsum_careplan'] += intval($activity->duration);
                    } else if (in_array($activity->type, $Progress)) {
                        $u20_patients[$patient_counter]['colsum_progress'] += intval($activity->duration);
                    } else if (in_array($activity->type, $RPM)) {
                        $u20_patients[$patient_counter]['colsum_rpm'] += intval($activity->duration);
                    } else if (in_array($activity->type, $TCM)) {
                        $u20_patients[$patient_counter]['colsum_tcc'] += intval($activity->duration);
                    } else {
                        $u20_patients[$patient_counter]['colsum_other'] += intval($activity->duration);
                    }
                    $u20_patients[$patient_counter]['colsum_total'] += intval($activity->duration);

                    if ($u20_patients[$patient_counter]['colsum_total'] >= 1200) {
                        unset($u20_patients[$patient_counter]);
                        continue 2;
                    }

                }
                $patient_counter++;
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

        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
            ->with('primaryProgram')
            ->get();

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
//            $monthly_time = intval($patient->getMonthlyTimeAttribute());
            $program = $patient->primaryProgram;
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

                if ($u20_patients[$act_count]['colsum_total'] < 1200) {
                    unset($u20_patients[$act_count]);
                }

                $act_count++;
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

        $feed = $this->service->progress($wpUser->ID);

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

        $feed = $this->service->careplan($wpUser->ID);

        return response()->json($feed);
    }

    public function viewPrintCareplan(Request $request, $patientId = false, CcdInsurancePolicyService $insurances)
    {
        if (!$patientId) {
            return "Patient Not Found..";
        }

        $patient = User::find($patientId);

        $careplan = $this->formatter->formatDataForViewPrintCareplanReport(array($patient));

        if (!$careplan) {
            return 'Careplan not found...';
        }

        $patient = User::find($patientId);

        $showInsuranceReviewFlag = $insurances->checkPendingInsuranceApproval($patient);

        return view('wpUsers.patient.careplan.print',
            [
                'patient' => $patient,
                'problems' => $careplan[$patientId]['problems'],
                'biometrics' => $careplan[$patientId]['bio_data'],
                'symptoms' => $careplan[$patientId]['symptoms'],
                'lifestyle' => $careplan[$patientId]['lifestyle'],
                'medications_monitor' => $careplan[$patientId]['medications'],
                'taking_medications' => $careplan[$patientId]['taking_meds'],
                'allergies' => $careplan[$patientId]['allergies'],
                'social' => $careplan[$patientId]['social'],
                'appointments' => $careplan[$patientId]['appointments'],
                'other' => $careplan[$patientId]['other'],
                'showInsuranceReviewFlag' => $showInsuranceReviewFlag,
            ]);
    }

    public function biometricsCharts(Request $request, $patientId = false)
    {

        $patient = User::find($patientId);

        $biometrics = ['Weight', 'Blood_Sugar', 'Blood_Pressure'];
        $biometrics_data = array();
        $biometrics_array = array();

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = $this->service->getBiometricsData($biometric, $patient);
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

        return view('wpUsers.patient.biometric-chart', ['patient' => $patient, 'biometrics_array' => $biometrics_array]);
    }

    public function excelReportT1()
    {
        // get all users with 1 condition
        $usersCondition = array();
        $problems = array();
        $cpmProblems = CpmProblem::all();
        if ($cpmProblems->count() > 0) {
            foreach ($cpmProblems as $cpmProblem) {
                $problems[$cpmProblem->id] = $cpmProblem->name;
                $problemNames[] = $cpmProblem->name;
            }
        }
        $careItemNames = CareItem::whereIn('display_name', $problemNames)->lists('display_name', 'id')->all();
        $careItems = CareItem::whereIn('display_name', $problemNames)->lists('id')->all();
        $careItemUserValues = CareItemUserValue::whereIn('care_item_id', $careItems)->where('value', 'Active')->get();
        if ($careItemUserValues->count() > 0) {
            foreach ($careItemUserValues as $careItemUserValue) {
                $usersCondition[$careItemUserValue->user_id] = $careItemNames[$careItemUserValue->care_item_id];
            }
        }
        $date = date('Y-m-d H:i:s');
        $users = User::all();

        Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $users, $usersCondition) {

            // Set the title
            $excel->setTitle('CLH Report T1');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T1');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use ($users, $usersCondition) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow(array(
                    'id', 'name', 'condition', 'program'
                ));
                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    $condition = 'N/A';
                    if (isset($usersCondition[$user->ID])) {
                        $condition = $usersCondition[$user->ID];
                    }
                    $programName = 'N/A';
                    $program = Program::find($user->program_id);
                    if ($program) {
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

    public function excelReportT2()
    {
        // get all users with paused ccm_status
        $users = User::with('patientInfo')
            ->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', '=', 'paused');
            })
            ->get();

        $date = date('Y-m-d H:i:s');

        Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $users) {

            // Set the title
            $excel->setTitle('CLH Report T2');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T2');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use ($users) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow(array(
                    'Patient ID',
                    'First Name',
                    'Last Name',
                    'Billing Provider',
                    'Phone',
                    'DOB',
                    'CCM Status',
                    'Gender',
                    'Address',
                    'City',
                    'State',
                    'Zip',
                    'CCM Time',
                    'Date Registered',
                    'Date Paused',
                    'Date Withdrawn',
                    'Site',
                    'Caller ID',
                    'Location ID',
                    'Location Name',
                    'Location Phone',
                    'Location Address',
                    'Location City',
                    'Location State',
                    'Location Zip'
                ));
                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    $billingProvider = User::find($user->billingProviderID);
                    if (!$billingProvider) {
                        $billingProvider = '';
                        $billingProviderPhone = '';
                    } else {
                        $billingProviderName = $billingProvider->display_name;
                        $billingProviderPhone = $billingProvider->phone;
                    }

                    $location = Location::find($user->patientInfo->preferred_contact_location);
                    if (!$location) {
                        $locationName = '';
                        $locationPhone = '';
                        $locationAddress = '';
                        $locationCity = '';
                        $locationState = '';
                        $locationZip = '';
                    } else {
                        $locationName = $location->name;
                        $locationPhone = $location->phone;
                        $locationAddress = $location->address_line_1;
                        $locationCity = $location->city;
                        $locationState = $location->state;
                        $locationZip = $location->postal_code;
                    }

                    $sheet->appendRow(array(
                        $user->ID,
                        $user->first_name,
                        $user->last_name,
                        $billingProviderName,
                        $user->phone,
                        $user->dob,
                        $user->ccm_status,
                        $user->gender,
                        $user->address,
                        $user->city,
                        $user->state,
                        $user->zip,
                        $user->monthlyTime,
                        $user->patientInfo->user_registered,
                        $user->patientInfo->date_paused,
                        $user->patientInfo->date_withdrawn,
                        $user->program_id,
                        'Caller ID', // provider_phone
                        $user->patientInfo->preferred_contact_location,
                        $locationName,
                        $locationPhone,
                        $locationAddress,
                        $locationCity,
                        $locationState,
                        $locationZip,
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

    public function excelReportT3()
    {
        // get all users with paused ccm_status
        $users = User::with('meta')
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->orWhere('name', 'care-center');
                    $query->orWhere('name', 'no-ccm-care-center');
                });
            })
            ->get();

        $date = date('Y-m-d H:i:s');

        Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $users) {

            // Set the title
            $excel->setTitle('CLH Report T3');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T3');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use ($users) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $userColumns = array('date');
                foreach ($users as $user) {
                    $userColumns[] = $user->display_name;
                }
                $sheet->appendRow($userColumns);

                // get array of dates
                $a = new DateTime('2016-03-30');
                $b = new DateTime(date('Y-m-d'));

                // to exclude the end date (so you just get dates between start and end date):
                // $b->modify('-1 day');

                $period = new DatePeriod($a, new DateInterval('P1D'), $b, DatePeriod::EXCLUDE_START_DATE);

                $sheetRows = array(); // so we can reverse after

                foreach ($period as $dt) {
                    //echo $dt->format('Y-m-d') .'<br />';

                    $rowUserValues = array($dt->format('Y-m-d'));
                    foreach ($users as $user) {
                        // get total activity time
                        $pageTime = PageTimer::whereBetween('start_time', [
                            $dt->format('Y-m-d') . ' 00:00:01', $dt->format('Y-m-d') . ' 23:59:59'
                        ])
                            ->where('provider_id', $user->ID)
                            ->where('activity_type', '!=', '')
                            ->sum('duration');

                        $rowUserValues[] = number_format((float)($pageTime / 60), 2, '.', '');;
                    }

                    $sheetRows[] = $rowUserValues;

                }

                $sheetRows = array_reverse($sheetRows);

                foreach ($sheetRows as $sheetRow) {
                    $sheet->appendRow($sheetRow);
                }

                //dd();
                //dd('done');
            });

            /*
            // Our second sheet
            $excel->sheet('Second sheet', function($sheet) {

            });
            */
        })->export('xls');
    }

    public function excelReportT4()
    {
        // get all patients
        $users = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'participant');
            })
            ->get();

        $date = date('Y-m-d H:i:s');

        Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $users) {

            // Set the title
            $excel->setTitle('CLH Report T4');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T4');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use ($users) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow(array(
                    'id', 'Provider', 'Program', 'CCM Status', 'DOB', 'Phone', 'Registered On', 'CCM', 'Patient Reached', 'Last Entered Note', 'Note Content', '2nd to last Entered Note', 'Note Content', '3rd to last Entered Note', 'Note Content'
                ));

                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    // provider
                    $billingProvider = User::find($user->billingProviderID);
                    $billingProviderName = '';
                    if ($billingProvider) {
                        $billingProviderName = $billingProvider->display_name;
                    }

                    // program
                    $programName = 'N/A';
                    $program = Program::find($user->program_id);
                    if ($program) {
                        $programName = $program->display_name;
                    }

                    // monthly time
                    $seconds = $user->curMonthActivityTime;
                    if ($seconds < 600) {
                        //continue 1;
                    }
                    $H = floor($seconds / 3600);
                    $i = ($seconds / 60) % 60;
                    $s = $seconds % 60;
                    $monthlyTime = sprintf("%03d:%02d", $i, $s);

                    $activity1comment = '';
                    $activity1status = '';
                    $activity1date = '';
                    $activity2comment = '';
                    $activity2status = '';
                    $activity2date = '';
                    $activity3comment = '';
                    $activity3status = '';
                    $activity3date = '';
                    $activities = $user->notes()
                        ->orderBy('performed_at', 'DESC')
                        ->limit(3)
                        ->get();
                    if ($activities->count() > 0) {
                        $a = 0;
                        foreach ($activities as $activity) {
                            $comment = $activity->body;
                            $callStatus = '';
                            $call = $activity->call()->first();
                            if ($call) {
                                $callStatus = $call->status;
                            }
                            if ($a == 0) {
                                $activity1comment = $activity->id . ' ' . $comment;
                                $activity1status = $callStatus;
                                $activity1date = $activity->performed_at;
                            }
                            if ($a == 1) {
                                $activity2comment = $activity->id . ' ' . $comment;
                                $activity2status = $callStatus;
                                $activity2date = $activity->performed_at;
                            }
                            if ($a == 2) {
                                $activity3comment = $activity->id . ' ' . $comment;
                                $activity3status = $callStatus;
                                $activity3date = $activity->performed_at;
                            }
                            $a++;
                        }
                    }
                    $sheet->appendRow(array(
                        $user->fullName,
                        $billingProviderName,
                        $programName,
                        $user->ccmStatus,
                        $user->birthDate,
                        $user->phone,
                        $user->registrationDate,
                        $monthlyTime,
                        $activity1status,
                        $activity1date,
                        $activity1comment,
                        $activity2date,
                        $activity2comment,
                        $activity3date,
                        $activity3comment
                    ));
                    $i++;
                }
            });
        })->export('xls');
    }
}