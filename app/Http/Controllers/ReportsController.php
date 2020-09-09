<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Contracts\ReportFormatter;
use App\Http\Requests\GetUnder20MinutesReport;
use App\Note;
use App\Relationships\PatientCareplanRelations;
use App\Repositories\PatientReadRepository;
use App\Services\CareplanAssessmentService;
use App\Services\CareplanService;
use App\Services\CCD\CcdInsurancePolicyService;
use App\Services\CPM\CpmProblemService;
use App\Services\PrintPausedPatientLettersService;
use App\Services\ReportsService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
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
        $RPM = [
            'Patient Alerts Review',
            'Patient Overview Review',
            'Biometrics Data Review',
            'Lifestyle Data Review',
            'Symptoms Data Review',
            'Assessments Scores Review',
            'Medications Data Review',
            'Input Observation',
        ];
        $TCM = [
            'Test (Scheduling, Communications, etc)',
            'Transitional Care Management Activities',
            'Call to Other Care Team Member',
            'Appointments',
        ];
        $Other = [
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
                ->select(
                    DB::raw(
                        '*,DATE(performed_at),provider_id, type, SUM(duration) as duration'
                    )
                )
                ->where('patient_id', $patient->id)
                ->whereBetween(
                    'performed_at',
                    [
                        $start,
                        $end,
                    ]
                )
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

        $reportData = 'data:'.json_encode(array_values($u20_patients)).'';

        $years = [];
        for ($i = 0; $i < 3; ++$i) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = [
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

    public function excelReportUnreachablePatients()
    {
        $users = $this->patientReadRepository->unreachable()->fetch();

        $date     = date('Y-m-d H:i:s');
        $filename = 'CLH-Report-'.$date.'.xls';

        $rows = [];

        $headings = [
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
        ];

        foreach ($users as $user) {
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

            $rows[] = [
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
            ];
        }

        return (new FromArray($filename, $rows, $headings))->download($filename);
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
                    $biometrics_array[$bio_name]['unit'] = $this->service->biometricsUnitMapping(
                        str_replace(
                            '_',
                            ' ',
                            $bio_name
                        )
                    );
                    $biometrics_array[$bio_name]['target'] = $this->service->getTargetValueForBiometric(
                        $bio_name,
                        $user,
                        false
                    );
                    $biometrics_array[$bio_name]['reading'] = intval($value->Avg);
                    if (intval($value->Avg) > $biometrics_array[$bio_name]['max']) {
                        $biometrics_array[$bio_name]['max'] = intval($value->Avg);
                    }
                    $biometrics_array[$bio_name]['data'] .= '{ id:'.$count.', Week:\''.$value->day.'\', Reading:'.intval(
                        $value->Avg
                    ).'} ,';
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

        $careplan = $this->formatter->formatDataForViewPrintCareplanReport($patient);

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

        $url = route('get.paused.letters.file').'?patientUserIds=';

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
        GetUnder20MinutesReport $request,
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

        if (isset($input['selectPractice'])) {
            $practiceId = $input['selectPractice'];
            $patients   = User::ofType('participant')
                ->with(
                    [
                        'primaryPractice' => function ($q) use ($practiceId) {
                            $q->where('id', '=', $practiceId);
                        },
                        'patientInfo',
                        'activities' => function ($q) use ($start, $end) {
                            $q->select(
                                DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration) as duration')
                            )
                                ->whereBetween(
                                    'performed_at',
                                    [
                                        $start,
                                        $end,
                                    ]
                                )
                                ->groupBy(DB::raw('provider_id, DATE(performed_at),type,lv_activities.id'))
                                ->orderBy('performed_at', 'desc');
                        },
                        'careTeamMembers' => function ($q) {
                            $q->with(['user' => function ($q) {
                                $q->without(['perms', 'roles'])
                                    ->select(['id', 'first_name', 'last_name', 'suffix', 'display_name']);
                            }])->where('member_user_id', auth()->user()->id)
                                ->whereIn(
                                    'type',
                                    [CarePerson::BILLING_PROVIDER, CarePerson::REGULAR_DOCTOR]
                                );
                        },
                    ]
                )
                ->whereHas('primaryPractice')
                ->whereHas('patientInfo')
                ->whereHas(
                    'patientSummaries',
                    function ($q) use ($time) {
                        $q->where('month_year', $time->copy()->startOfMonth()->toDateString())
                            ->where('total_time', '<', 1200);
                    }
                )
                ->get();
        } else {
            $patients = collect();
        }

        $practices        = auth()->user()->practices(true)->select(['id', 'display_name'])->get();
        $practiceSelected = $input['selectPractice'] ?? null;

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
        $RPM = [
            'Patient Alerts Review',
            'Patient Overview Review',
            'Biometrics Data Review',
            'Lifestyle Data Review',
            'Symptoms Data Review',
            'Assessments Scores Review',
            'Medications Data Review',
            'Input Observation',
        ];
        $TCM = [
            'Test (Scheduling, Communications, etc)',
            'Transitional Care Management Activities',
            'Call to Other Care Team Member',
            'Appointments',
        ];
        $Other = [
            'other',
            'Medication Reconciliation',
        ];

        $patient_counter = 0;
        foreach ($patients as $patient) {
            $u20_patients[$patient_counter]['site']            = $patient->primaryPractice->display_name;
            $u20_patients[$patient_counter]['colsum_careplan'] = 0;
            $u20_patients[$patient_counter]['colsum_changes']  = 0;
            $u20_patients[$patient_counter]['colsum_progress'] = 0;
            $u20_patients[$patient_counter]['colsum_rpm']      = 0;
            $u20_patients[$patient_counter]['colsum_tcc']      = 0;
            $u20_patients[$patient_counter]['colsum_other']    = 0;
            $u20_patients[$patient_counter]['colsum_total']    = 0;
            $u20_patients[$patient_counter]['ccm_status']      = ucwords($patient->getCcmStatus());
            $u20_patients[$patient_counter]['dob']             = Carbon::parse($patient->getBirthDate())->format(
                'm/d/Y'
            );
            $u20_patients[$patient_counter]['mrn']                 = $patient->patientInfo->mrn_number;
            $u20_patients[$patient_counter]['patient_name']        = $patient->getFullName();
            $u20_patients[$patient_counter]['patient_id']          = $patient->id;
            $u20_patients[$patient_counter]['practice_id']         = $patient->program_id;
            $u20_patients[$patient_counter]['location_id']         = optional($patient->patientInfo)->preferred_contact_location;
            $u20_patients[$patient_counter]['billing_provider_id'] = $patient->getBillingProviderId();
            $acts                                                  = $patient->activities;

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
        $reportData = 'data:'.json_encode(array_values($u20_patients)).'';

        $years = [];
        for ($i = 0; $i < 3; ++$i) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = [
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
            'practices'           => $practices,
            'practice_selected'   => $practiceSelected,
            'month_selected'      => $month_selected,
            'month_selected_text' => $month_selected_text,
            'year_selected'       => $year_selected,
            'months'              => $months,
            'data'                => $act_data,
        ];

        return view('reports.u20', $data);
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
        CcdInsurancePolicyService $insurances,
        CareplanService $careplanService,
        Request $request,
        $patientId = false
    ) {
        ini_set('max_execution_time', 150);
        ini_set('memory_limit', '512M');

        if ( ! $patientId) {
            return response('Patient Not Found..', 400);
        }

        /** @var User $patient */
        $patient = User::with(PatientCareplanRelations::get())->findOrFail($patientId);

        if (CarePlan::PDF == $patient->getCareplanMode()) {
            return redirect()->route('patient.pdf.careplan.print', ['patientId' => $patientId]);
        }

        $careplan = $this->formatter->formatDataForViewPrintCareplanReport($patient);

        if ( ! $patient->carePlan || ! $careplan) {
            return response('Careplan not found...', 400);
        }

        /** @var User $user */
        $user                           = auth()->user();
        $showReadyForDrButton           = $patient->carePlan->shouldRnApprove($user);
        $readyForDrButtonDisabled       = false;
        $readyForDrButtonAlreadyClicked = false;
        if ($showReadyForDrButton) {
            $readyForDrButtonDisabled = ! Note::whereStatus(Note::STATUS_DRAFT)
                ->where('patient_id', '=', $patient->id)
                ->where('successful_clinical_call', '=', 1)
                ->exists();

            $approvedBy                     = $request->session()->get(ProviderController::SESSION_RN_APPROVED_KEY, null);
            $readyForDrButtonAlreadyClicked = $approvedBy && $approvedBy == $user->id;
        }

//        To phase out
//        $showInsuranceReviewFlag = $insurances->checkPendingInsuranceApproval($patient);
        $showInsuranceReviewFlag = false;

        $skippedAssessment = $request->has('skippedAssessment');

        $recentSubmission = $request->input('recentSubmission') ?? false;

        $cpmMiscs = CpmMisc::pluck('id', 'name');

        $args = [
            'patient'                 => $patient,
            'careplanStatus'          => $patient->carePlan->status,
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
            'careplan'                => array_merge(
                $careplanService->careplan($patient),
                //vue front end expects this format
                ['other' => [
                    ['name' => $careplan[$patientId]['other']],
                ],
                ]
            ),
            'socialServicesMiscId'           => $cpmMiscs[CpmMisc::SOCIAL_SERVICES],
            'othersMiscId'                   => $cpmMiscs[CpmMisc::OTHER],
            'rnApprovalEnabled'              => $patient->carePlan->isRnApprovalEnabled(),
            'showReadyForDrButton'           => $showReadyForDrButton,
            'readyForDrButtonDisabled'       => $readyForDrButtonDisabled,
            'readyForDrButtonAlreadyClicked' => $readyForDrButtonAlreadyClicked,
        ];

        return view(
            'wpUsers.patient.careplan.print',
            $args
        );
    }
}
