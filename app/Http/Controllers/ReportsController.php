<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CareItem;
use App\CarePlan;
use App\Contracts\ReportFormatter;
use App\Location;
use App\Models\CPM\CpmProblem;
use App\PageTimer;
use App\Practice;
use App\Repositories\PatientReadRepository;
use App\Services\CareplanAssessmentService;
use App\Services\CareplanService;
use App\Services\CCD\CcdInsurancePolicyService;
use App\Services\CPM\CpmProblemService;
use App\Services\PrintPausedPatientLettersService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    private $assessmentService;
    private $formatter;
    private $patientReadRepository;
    private $printPausedPatientLettersService;
    private $service;

    public function __construct(
        CareplanAssessmentService $assessmentService,
        ReportsService $service,
        ReportFormatter $formatter,
        Request $request,
        PrintPausedPatientLettersService $printPausedPatientLettersService,
        PatientReadRepository $patientReadRepository
    ) {
        $this->service                          = $service;
        $this->formatter                        = $formatter;
        $this->assessmentService                = $assessmentService;
        $this->printPausedPatientLettersService = $printPausedPatientLettersService;
        $this->patientReadRepository            = $patientReadRepository;
    }

    public function billing(
        Request $request,
        $patientId = false
    ) {
        $input = $request->all();

        if (isset($input['selectMonth'])) {
            $time                = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $start               = $time->startOfMonth()->format('Y-m-d');
            $end                 = $time->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected      = $time->format('m');
            $year_selected       = $time->format('Y');
        } else {
            $time                = Carbon::now();
            $start               = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end                 = Carbon::now()->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected      = $time->format('m');
            $year_selected       = $time->format('Y');
        }

        $patients = User::intersectPracticesWith(auth()->user())
                        ->ofType('participant')
                        ->with('primaryPractice')
                        ->get();

        $u20_patients      = [];
        $billable_patients = [];

        // ROLLUP CATEGORIES
        $CarePlan  = [
            'Edit/Modify Care Plan',
            'Initial Care Plan Setup',
            'Care Plan View/Print',
            'Patient History Review',
            'Patient Item Detail Review',
            'Review Care Plan (offline)',
        ];
        $Progress  = [
            'Review Patient Progress (offline)',
            'Progress Report Review/Print',
        ];
        $RPM       = [
            'Patient Alerts Review',
            'Patient Overview Review',
            'Biometrics Data Review',
            'Lifestyle Data Review',
            'Symptoms Data Review',
            'Assessments Scores Review',
            'Medications Data Review',
            'Input Observation',
        ];
        $TCM       = [
            'Test (Scheduling, Communications, etc)',
            'Transitional Care Management Activities',
            'Call to Other Care Team Member',
            'Appointments',
        ];
        $Other     = [
            'other',
            'Medication Reconciliation',
        ];
        $act_count = 0;

        foreach ($patients as $patient) {
            $u20_patients[$act_count]['site']            = $patient->primaryPractice->display_name;
            $u20_patients[$act_count]['colsum_careplan'] = 0;
            $u20_patients[$act_count]['colsum_changes']  = 0;
            $u20_patients[$act_count]['colsum_progress'] = 0;
            $u20_patients[$act_count]['colsum_rpm']      = 0;
            $u20_patients[$act_count]['colsum_tcc']      = 0;
            $u20_patients[$act_count]['colsum_other']    = 0;
            $u20_patients[$act_count]['colsum_total']    = 0;
            $u20_patients[$act_count]['ccm_status']      = ucwords($patient->getCcmStatus());
            $u20_patients[$act_count]['dob']             = Carbon::parse($patient->getBirthDate())->format('m/d/Y');
            $u20_patients[$act_count]['patient_name']    = $patient->getFullName();
            $provider                                    = User::find(intval($patient->getBillingProviderId()));
            if ($provider) {
                $u20_patients[$act_count]['provider_name'] = $provider->getFullName();
            } else {
                $u20_patients[$act_count]['provider_name'] = '';
            }
            $u20_patients[$act_count]['patient_id'] = $patient->id;
            $acts                                   = DB::table('lv_activities')
                                                        ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration) as duration'))
                                                        ->where('patient_id', $patient->id)
                                                        ->whereBetween('performed_at', [
                                                            $start,
                                                            $end,
                                                        ])
                                                        ->where('duration', '>', 1200)
                                                        ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                                                        ->orderBy('performed_at', 'desc')
                                                        ->get();

            foreach ($acts as $activity) {
                if (in_array($activity->type, $CarePlan)) {
                    $u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
                } else {
                    if (in_array($activity->type, $Progress)) {
                        $u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
                    } else {
                        if (in_array($activity->type, $RPM)) {
                            $u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
                        } else {
                            if (in_array($activity->type, $TCM)) {
                                $u20_patients[$act_count]['colsum_tcc'] += intval($activity->duration);
                            } else {
                                $u20_patients[$act_count]['colsum_other'] += intval($activity->duration);
                            }
                        }
                    }
                }
                $u20_patients[$act_count]['colsum_total'] += intval($activity->duration);
            }

            if ($u20_patients[$act_count]['colsum_total'] < 1200) {
                unset($u20_patients[$act_count]);
            }

            ++$act_count;
        }

        $reportData = 'data:' . json_encode(array_values($u20_patients)) . '';

        $years = [];
        for ($i = 0; $i < 3; ++$i) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months   = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ];
        $act_data = true;
        if (null == $u20_patients) {
            $act_data = false;
        }

        $data = [
            'activity_json'       => $reportData,
            'years'               => array_reverse($years),
            'month_selected'      => $month_selected,
            'year_selected'       => $year_selected,
            'month_selected_text' => $month_selected_text,
            'months'              => $months,
            'data'                => $act_data,
        ];

        return view('reports.billing', $data);
    }

    public function biometricsCharts(
        Request $request,
        $patientId = false
    ) {
        $patient = User::find($patientId);

        $biometrics       = [
            'Weight',
            'Blood_Sugar',
            'Blood_Pressure',
        ];
        $biometrics_data  = [];
        $biometrics_array = [];

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = $this->service->getBiometricsData($biometric, $patient);
        }            //debug($biometrics_data);

        foreach ($biometrics_data as $key => $value) {
            $bio_name                            = $key;
            $count                               = 1;
            $biometrics_array[$bio_name]['data'] = '';
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['data'] .= '{ id:' . $count . ', Week:\'' . $value->day . '\', Reading:' . intval($value->Avg) . '} ,';
                    ++$count;
                }
            } else {
                //no data
                $biometrics_array[$bio_name]['data'] = '';
            }//debug($biometrics_array);
        }

        return view('wpUsers.patient.biometric-chart', [
            'patient'          => $patient,
            'biometrics_array' => $biometrics_array,
        ]);
    }

    public function excelReportT1()
    {
        // get all users with 1 condition
        $usersCondition = [];
        $problems       = [];
        $cpmProblems    = CpmProblem::all();
        if ($cpmProblems->count() > 0) {
            foreach ($cpmProblems as $cpmProblem) {
                $problems[$cpmProblem->id] = $cpmProblem->name;
                $problemNames[]            = $cpmProblem->name;
            }
        }
        $careItemNames      = CareItem::whereIn('display_name', $problemNames)->pluck('display_name', 'id')->all();
        $careItems          = CareItem::whereIn('display_name', $problemNames)->pluck('id')->all();
        $careItemUserValues = CareItemUserValue::whereIn('care_item_id', $careItems)->where('value', 'Active')->get();
        if ($careItemUserValues->count() > 0) {
            foreach ($careItemUserValues as $careItemUserValue) {
                $usersCondition[$careItemUserValue->user_id] = $careItemNames[$careItemUserValue->care_item_id];
            }
        }
        $date  = date('Y-m-d H:i:s');
        $users = User::all();

        Excel::create('CLH-Report-' . $date, function ($excel) use (
            $date,
            $users,
            $usersCondition
        ) {
            // Set the title
            $excel->setTitle('CLH Report T1');

            // Chain the setters
            $excel->setCreator('CLH System')
                  ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T1');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use (
                $users,
                $usersCondition
            ) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow([
                    'id',
                    'name',
                    'condition',
                    'program',
                ]);
                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    $condition = 'N/A';
                    if (isset($usersCondition[$user->id])) {
                        $condition = $usersCondition[$user->id];
                    }
                    $programName = 'N/A';
                    $program     = Practice::find($user->program_id);
                    if ($program) {
                        $programName = $program->display_name;
                    }
                    $sheet->appendRow([
                        $user->id,
                        $user->getFullName(),
                        $condition,
                        $programName,
                    ]);
                    ++$i;
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
        $users = $this->patientReadRepository->unreachable()->fetch();

        $date = date('Y-m-d H:i:s');

        Excel::create('CLH-Report-' . $date, function ($excel) use (
            $date,
            $users
        ) {
            // Set the title
            $excel->setTitle('CLH Report T2');

            // Chain the setters
            $excel->setCreator('CLH System')
                  ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T2');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use (
                $users
            ) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow([
                    'Patient id',
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
                    'Date Unreachable',
                    'Site',
                    'Caller id',
                    'Location id',
                    'Location Name',
                    'Location Phone',
                    'Location Address',
                    'Location City',
                    'Location State',
                    'Location Zip',
                ]);
                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    $billingProvider = User::find($user->getBillingProviderId());
                    //is billingProviderPhone to be used anywhere?
                    if ( ! $billingProvider) {
                        $billingProviderName  = '';
                        $billingProviderPhone = '';
                    } else {
                        $billingProviderName  = $billingProvider->display_name;
                        $billingProviderPhone = $billingProvider->getPhone();
                    }

                    $location = Location::find($user->getPreferredContactLocation());
                    if ( ! $location) {
                        $locationName    = '';
                        $locationPhone   = '';
                        $locationAddress = '';
                        $locationCity    = '';
                        $locationState   = '';
                        $locationZip     = '';
                    } else {
                        $locationName    = $location->name;
                        $locationPhone   = $location->phone;
                        $locationAddress = $location->address_line_1;
                        $locationCity    = $location->city;
                        $locationState   = $location->state;
                        $locationZip     = $location->postal_code;
                    }

                    $sheet->appendRow([
                        $user->id,
                        $user->getFirstName(),
                        $user->getLastName(),
                        $billingProviderName,
                        $user->getPhone(),
                        $user->dob,
                        $user->getCcmStatus(),
                        $user->getGender(),
                        $user->address,
                        $user->city,
                        $user->state,
                        $user->zip,
                        $user->monthlyTime,
                        $user->patientInfo->user_registered,
                        $user->patientInfo->date_paused,
                        $user->patientInfo->date_withdrawn,
                        $user->patientInfo->date_unreachable,
                        $user->program_id,
                        'Caller id',
                        // provider_phone
                        $user->getPreferredContactLocation(),
                        $locationName,
                        $locationPhone,
                        $locationAddress,
                        $locationCity,
                        $locationState,
                        $locationZip,
                    ]);
                    ++$i;
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

        Excel::create('CLH-Report-' . $date, function ($excel) use (
            $date,
            $users
        ) {
            // Set the title
            $excel->setTitle('CLH Report T3');

            // Chain the setters
            $excel->setCreator('CLH System')
                  ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T3');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use (
                $users
            ) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $userColumns = ['date'];
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

                $sheetRows = []; // so we can reverse after

                foreach ($period as $dt) {
                    //echo $dt->format('Y-m-d') .'<br />';

                    $rowUserValues = [$dt->format('Y-m-d')];
                    foreach ($users as $user) {
                        // get total activity time
                        $pageTime = PageTimer::whereBetween('start_time', [
                            $dt->format('Y-m-d') . ' 00:00:01',
                            $dt->format('Y-m-d') . ' 23:59:59',
                        ])
                                             ->where('provider_id', $user->id)
                                             ->where('activity_type', '!=', '')
                                             ->sum('duration');

                        $rowUserValues[] = number_format((float)($pageTime / 60), 2, '.', '');
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

        Excel::create('CLH-Report-' . $date, function ($excel) use (
            $date,
            $users
        ) {
            // Set the title
            $excel->setTitle('CLH Report T4');

            // Chain the setters
            $excel->setCreator('CLH System')
                  ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T4');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use (
                $users
            ) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $sheet->appendRow([
                    'id',
                    'Provider',
                    'Practice',
                    'CCM Status',
                    'DOB',
                    'Phone',
                    'Registered On',
                    'CCM',
                    'Patient Reached',
                    'Last Entered Note',
                    'Note Content',
                    '2nd to last Entered Note',
                    'Note Content',
                    '3rd to last Entered Note',
                    'Note Content',
                ]);

                foreach ($users as $user) {
                    if ($i > 2000000) {
                        continue 1;
                    }

                    // provider
                    $billingProvider     = User::find($user->billingProviderID);
                    $billingProviderName = '';
                    if ($billingProvider) {
                        $billingProviderName = $billingProvider->display_name;
                    }

                    // program
                    $programName = 'N/A';
                    $program     = Practice::find($user->program_id);
                    if ($program) {
                        $programName = $program->display_name;
                    }

                    // monthly time
                    $seconds = $user->curMonthActivityTime;
                    if ($seconds < 600) {
                        //continue 1;
                    }
                    $H           = floor($seconds / 3600);
                    $i           = ($seconds / 60) % 60;
                    $s           = $seconds % 60;
                    $monthlyTime = sprintf('%03d:%02d', $i, $s);

                    $activity1comment = '';
                    $activity1status  = '';
                    $activity1date    = '';
                    $activity2comment = '';
                    $activity2status  = '';
                    $activity2date    = '';
                    $activity3comment = '';
                    $activity3status  = '';
                    $activity3date    = '';
                    $activities       = $user->notes()
                                             ->orderBy('performed_at', 'DESC')
                                             ->limit(3)
                                             ->get();
                    if ($activities->count() > 0) {
                        $a = 0;
                        foreach ($activities as $activity) {
                            $comment    = $activity->body;
                            $callStatus = '';
                            $call       = $activity->call()->first();
                            if ($call) {
                                $callStatus = $call->status;
                            }
                            if (0 == $a) {
                                $activity1comment = $activity->id . ' ' . $comment;
                                $activity1status  = $callStatus;
                                $activity1date    = $activity->performed_at;
                            }
                            if (1 == $a) {
                                $activity2comment = $activity->id . ' ' . $comment;
                                $activity2status  = $callStatus;
                                $activity2date    = $activity->performed_at;
                            }
                            if (2 == $a) {
                                $activity3comment = $activity->id . ' ' . $comment;
                                $activity3status  = $callStatus;
                                $activity3date    = $activity->performed_at;
                            }
                            ++$a;
                        }
                    }
                    $sheet->appendRow([
                        $user->getFullName(),
                        $billingProviderName,
                        $programName,
                        $user->getCcmStatus(),
                        $user->getBirthDate(),
                        $user->getPhone(),
                        $user->getRegistrationDate(),
                        $monthlyTime,
                        $activity1status,
                        $activity1date,
                        $activity1comment,
                        $activity2date,
                        $activity2comment,
                        $activity3date,
                        $activity3comment,
                    ]);
                    ++$i;
                }
            });
        })->export('xls');
    }

    public function getPausedLettersFile(Request $request)
    {
        if ( ! $request->has('patientUserIds')) {
            throw new \InvalidArgumentException('patientUserIds is a required parameter', 422);
        }

        $viewOnly = $request->has('view');

        $userIdsToPrint = explode(',', $request['patientUserIds']);

        $fullPathToFile = $this->printPausedPatientLettersService->makePausedLettersPdf($userIdsToPrint, $viewOnly);

        return response()->file($fullPathToFile);
    }

    //PROGRESS REPORT
    public function index(
        Request $request,
        $patientId = false
    ) {
        $user             = User::find($patientId);
        $treating         = (app(CpmProblemService::class))->getDetails($user);
        $biometrics       = $this->service->getBiometricsToMonitor($user);
        $biometrics_data  = [];
        $biometrics_array = [];

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = $this->service->getBiometricsData(str_replace(' ', '_', $biometric), $user);
        }

        foreach ($biometrics_data as $key => $value) {
            $value    = $value->all();
            $bio_name = $key;
            if (null != $value) {
                $first   = reset($value);
                $last    = end($value);
                $changes = $this->service
                    ->biometricsIndicators(
                        intval($last->Avg),
                        intval($first->Avg),
                        $bio_name,
                        (new ReportsService())->getTargetValueForBiometric($bio_name, $user)
                    );

                $biometrics_array[$bio_name]['change']      = $changes['change'];
                $biometrics_array[$bio_name]['progression'] = $changes['progression'];
                $biometrics_array[$bio_name]['status']      = (isset($changes['status']))
                    ? $changes['status']
                    : 'Unchanged';
                //$changes['bio']= $bio_name;debug($changes);
                $biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);
            }//debug($biometrics_array);

            $count                               = 1;
            $biometrics_array[$bio_name]['data'] = '';
            $biometrics_array[$bio_name]['max']  = -1;
            //$first = reset($array);
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['unit']    = $this->service->biometricsUnitMapping(str_replace(
                        '_',
                        ' ',
                        $bio_name
                    ));
                    $biometrics_array[$bio_name]['target']  = $this->service->getTargetValueForBiometric(
                        $bio_name,
                        $user,
                        false
                    );
                    $biometrics_array[$bio_name]['reading'] = intval($value->Avg);
                    if (intval($value->Avg) > $biometrics_array[$bio_name]['max']) {
                        $biometrics_array[$bio_name]['max'] = intval($value->Avg);
                    }
                    $biometrics_array[$bio_name]['data'] .= '{ id:' . $count . ', Week:\'' . $value->day . '\', Reading:' . intval($value->Avg) . '} ,';
                    ++$count;
                }
            } else {
                //no data
                unset($biometrics_array[$bio_name]);
            }
        }//dd($biometrics_array);

        // get provider
        $provider = User::find($user->getLeadContactID());

        //Medication Tracking:
        $medications = $this->service->getMedicationStatus($user, false);

        $data = [
            'treating'                => $treating,
            'patientId'               => $patientId,
            'patient'                 => $user,
            'provider'                => $provider,
            'medications'             => $medications,
            'tracking_biometrics'     => $biometrics_array,
            'noLiveCountTimeTracking' => true,
        ];

        return view('wpUsers.patient.progress', $data);
    }

    public function makeAssessment(
        Request $request,
        $patientId = false,
        $approverId = null,
        CcdInsurancePolicyService $insurances
    ) {
        if ( ! $patientId) {
            return 'Patient Not Found..';
        }

        $patient = User::with('carePlan')->find($patientId);

        if ( ! $patient) {
            return 'Patient Not Found..';
        }

        // if ( ! $patient->isCcmEligible()) {
        //     return redirect()->route('patient.careplan.print', ['patientId' => $patientId]);
        // }

        $careplan = $this->formatter->formatDataForViewPrintCareplanReport([$patient]);

        if ( ! $careplan) {
            return 'Careplan not found...';
        }

        $showInsuranceReviewFlag = $insurances->checkPendingInsuranceApproval($patient);
        $editable                = true;

        $assessmentQuery = $this->assessmentService->repo()->model()->where(['careplan_id' => $patientId]);
        if ($approverId) {
            $assessmentQuery = $assessmentQuery->where(['provider_approver_id' => $approverId]);
        }

        $assessment = $assessmentQuery->first();

        if ($assessment) {
            $assessment->unload();
            $editable = $patient->isCcmEligible() || ($assessment->provider_approver_id != auth()->user()->id);
        }

        $approver = $assessment
            ? $assessment->approver()->first()
            : null;

        return view(
            'wpUsers.patient.careplan.assessment',
            [
                'patient'                 => $patient,
                'problems'                => $careplan[$patientId]['problems'],
                'problemNames'            => $careplan[$patientId]['problem'],
                'biometrics'              => $careplan[$patientId]['bio_data'],
                'symptoms'                => $careplan[$patientId]['symptoms'],
                'lifestyle'               => $careplan[$patientId]['lifestyle'],
                'medications_monitor'     => $careplan[$patientId]['medications'],
                'taking_medications'      => $careplan[$patientId]['taking_meds'],
                'allergies'               => $careplan[$patientId]['allergies'],
                'social'                  => $careplan[$patientId]['social'],
                'appointments'            => $careplan[$patientId]['appointments'],
                'other'                   => $careplan[$patientId]['other'],
                'showInsuranceReviewFlag' => $showInsuranceReviewFlag,
                'assessment'              => $assessment,
                'approver'                => $approver,
                'editable'                => $editable,
            ]
        );
    }

    public function pausedPatientsLetterPrintList()
    {
        $patients = false;

        $pausedPatients = $this->printPausedPatientLettersService->getPausedPatients();

        if ($pausedPatients->isNotEmpty()) {
            $patients = $pausedPatients->toJson();
        }

        $url = route('get.paused.letters.file') . '?patientUserIds=';

        return view('patient.printPausedPatientsLetters', compact(['patients', 'url']));
    }

    public function progress(
        Request $request,
        $id = false
    ) {
        if ('mobi' == $request->header('Client')) {
            // get and validate current user
            \JWTAuth::setIdentifier('id');
            $wpUser = \JWTAuth::parseToken()->authenticate();
            if ( ! $wpUser) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } else {
            // get user
            $wpUser = User::find($id);
            if ( ! $wpUser) {
                return response('User not found', 401);
            }
        }

        $feed = $this->service->progress($wpUser->id);

        return json_encode($feed);
    }

    public function u20(
        Request $request,
        $patientId = false
    ) {
        $input = $request->all();

        if (isset($input['selectMonth'])) {
            $time                = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $month_selected      = $time->format('m');
            $month_selected_text = $time->format('F');
            $year_selected       = $time->format('Y');
            $start               = $time->startOfMonth()->toDateString();
            $end                 = $time->endOfMonth()->toDateString();
        } else {
            $time                = Carbon::now();
            $month_selected      = $time->format('m');
            $year_selected       = $time->format('Y');
            $month_selected_text = $time->format('F');
            $start               = Carbon::now()->startOfMonth()->toDateString();
            $end                 = Carbon::now()->endOfMonth()->toDateString();
        }

        $patients = User::intersectPracticesWith(auth()->user())
                        ->ofType('participant')
                        ->with([
                            'primaryPractice',
                            'activities' => function ($q) use ($start, $end) {
                                $q->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration) as duration'))
                                  ->whereBetween('performed_at', [
                                      $start,
                                      $end,
                                  ])
                                  ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                                  ->orderBy('performed_at', 'desc');
                            },
                        ])
                        ->whereHas('patientSummaries', function ($q) use ($time) {
                            $q->where('month_year', $time->copy()->startOfMonth()->toDateString())
                              ->where('total_time', '<', 1200);
                        })
                        ->get();

        $u20_patients = [];

        // ROLLUP CATEGORIES
        $CarePlan = [
            'Edit/Modify Care Plan',
            'Initial Care Plan Setup',
            'Care Plan View/Print',
            'Patient History Review',
            'Patient Item Detail Review',
            'Review Care Plan (offline)',
        ];
        $Progress = [
            'Review Patient Progress (offline)',
            'Progress Report Review/Print',
        ];
        $RPM      = [
            'Patient Alerts Review',
            'Patient Overview Review',
            'Biometrics Data Review',
            'Lifestyle Data Review',
            'Symptoms Data Review',
            'Assessments Scores Review',
            'Medications Data Review',
            'Input Observation',
        ];
        $TCM      = [
            'Test (Scheduling, Communications, etc)',
            'Transitional Care Management Activities',
            'Call to Other Care Team Member',
            'Appointments',
        ];
        $Other    = [
            'other',
            'Medication Reconciliation',
        ];

        $patient_counter = 0;
        foreach ($patients as $patient) {
            $u20_patients[$patient_counter]['site'] = $patient->primaryPractice->display_name;

            $u20_patients[$patient_counter]['colsum_careplan'] = 0;
            $u20_patients[$patient_counter]['colsum_changes']  = 0;
            $u20_patients[$patient_counter]['colsum_progress'] = 0;
            $u20_patients[$patient_counter]['colsum_rpm']      = 0;
            $u20_patients[$patient_counter]['colsum_tcc']      = 0;
            $u20_patients[$patient_counter]['colsum_other']    = 0;
            $u20_patients[$patient_counter]['colsum_total']    = 0;
            $u20_patients[$patient_counter]['ccm_status']      = ucwords($patient->getCcmStatus());
            $u20_patients[$patient_counter]['dob']             = Carbon::parse($patient->getBirthDate())->format('m/d/Y');
            $u20_patients[$patient_counter]['patient_name']    = $patient->getFullName();
            $u20_patients[$patient_counter]['patient_id']      = $patient->id;
            $acts                                              = $patient->activities;

            foreach ($acts as $activity) {
                if (in_array($activity->type, $CarePlan)) {
                    $u20_patients[$patient_counter]['colsum_careplan'] += intval($activity->duration);
                } else {
                    if (in_array($activity->type, $Progress)) {
                        $u20_patients[$patient_counter]['colsum_progress'] += intval($activity->duration);
                    } else {
                        if (in_array($activity->type, $RPM)) {
                            $u20_patients[$patient_counter]['colsum_rpm'] += intval($activity->duration);
                        } else {
                            if (in_array($activity->type, $TCM)) {
                                $u20_patients[$patient_counter]['colsum_tcc'] += intval($activity->duration);
                            } else {
                                $u20_patients[$patient_counter]['colsum_other'] += intval($activity->duration);
                            }
                        }
                    }
                }
                $u20_patients[$patient_counter]['colsum_total'] += intval($activity->duration);

                if ($u20_patients[$patient_counter]['colsum_total'] >= 1200) {
                    unset($u20_patients[$patient_counter]);
                    continue 2;
                }
            }
            ++$patient_counter;
        }
        $reportData = 'data:' . json_encode(array_values($u20_patients)) . '';

        $years = [];
        for ($i = 0; $i < 3; ++$i) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months   = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ];
        $act_data = true;
        if (null == $u20_patients) {
            $act_data = false;
        }

        $data = [
            'activity_json'       => $reportData,
            'years'               => array_reverse($years),
            'month_selected'      => $month_selected,
            'month_selected_text' => $month_selected_text,
            'year_selected'       => $year_selected,
            'months'              => $months,
            'data'                => $act_data,
        ];

        return view('reports.u20', $data);
    }

    public function viewPdfCarePlan(
        Request $request,
        $patientId = false
    ) {
        if ( ! $patientId) {
            return 'Patient Not Found..';
        }

        $patient = User::find($patientId);

        return view('patient.careplan.view-pdf-careplan', compact(['patient']));
    }

    public function viewPrintCareplan(
        Request $request,
        $patientId = false,
        CcdInsurancePolicyService $insurances,
        CareplanService $careplanService
    ) {
        if ( ! $patientId) {
            return 'Patient Not Found..';
        }

        $patient = User::with('carePlan')->find($patientId);

        if (CarePlan::PDF == $patient->getCareplanMode()) {
            return redirect()->route('patient.pdf.careplan.print', ['patientId' => $patientId]);
        }

        $careplan = $this->formatter->formatDataForViewPrintCareplanReport([$patient]);

        if ( ! $careplan) {
            return 'Careplan not found...';
        }

        $showInsuranceReviewFlag = $insurances->checkPendingInsuranceApproval($patient);

        $skippedAssessment = $request->has('skippedAssessment');

        $recentSubmission = $request->input('recentSubmission') ?? false;

        return view(
            'wpUsers.patient.careplan.print',
            [
                'patient'                 => $patient,
                'problems'                => $careplan[$patientId]['problems'],
                'problemNames'            => $careplan[$patientId]['problem'],
                'biometrics'              => $careplan[$patientId]['bio_data'],
                'symptoms'                => $careplan[$patientId]['symptoms'],
                'lifestyle'               => $careplan[$patientId]['lifestyle'],
                'medications_monitor'     => $careplan[$patientId]['medications'],
                'taking_medications'      => $careplan[$patientId]['taking_meds'],
                'allergies'               => $careplan[$patientId]['allergies'],
                'social'                  => $careplan[$patientId]['social'],
                'appointments'            => $careplan[$patientId]['appointments'],
                'other'                   => $careplan[$patientId]['other'],
                'showInsuranceReviewFlag' => $showInsuranceReviewFlag,
                'skippedAssessment'       => $skippedAssessment,
                'recentSubmission'        => $recentSubmission,
                'careplan'                => $careplanService->careplan($patientId),
            ]
        );
    }

    public function viewCareDocumentsPage(
        Request $request,
        $patientId = false
    ) {
        if ( ! $patientId) {
            return 'Patient Not Found..';
        }

        $patient = User::find($patientId);

        return view('patient.care-docs.index', compact(['patient']));

    }
}
