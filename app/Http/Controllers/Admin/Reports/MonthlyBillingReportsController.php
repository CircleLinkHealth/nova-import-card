<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Call;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Patient;
use App\PatientMonthlySummary;
use App\Practice;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Facades\Datatables;

class MonthlyBillingReportsController extends Controller
{
    public function create()
    {
        $programs = Practice::orderBy('id', 'desc')->pluck('display_name', 'id')->all();

        return view('admin.monthlyBillingReports.create', compact(['programs']));
    }

    public function makeMonthlyReport(Request $request)
    {
        $worksheets = [];
        //whether over or under 20 minutes
        $under = $request->input('under', false);

        $overOrUnder = $under
            ? '<'
            : '>';

        $ccmTimeMin = $request->input('ccm_time_minutes', 20);

        //20 mins in seconds
        $ccmTimeSec = ($ccmTimeMin * 60);
        $month = $request->input('month');
        $year = $request->input('year');
        $patientRole = Role::whereName('participant')->first();
        $programs = $request->input('programs');

        $ccmStatuses = $request->input('status', []);

        foreach ($programs as $programId) {
            $program = Practice::find($programId);

            $time = Carbon::createFromDate($year, $month, 15);
            $start = $time->startOfMonth()->startOfDay()->format("Y-m-d H:i:s");
            $end = $time->endOfMonth()->endOfDay()->format("Y-m-d H:i:s");

            $patientsOver20MinsQuery = DB::table('lv_activities')
                ->select(DB::raw('patient_id, SUM(duration) as ccmTime'))
                ->whereBetween('performed_at', [
                    $start,
                    $end,
                ])
                ->having('ccmTime', $overOrUnder, $ccmTimeSec)
                ->groupBy('patient_id');

            $patientsOver20Mins = (new Collection($patientsOver20MinsQuery->get()))->keyBy('patient_id');
            $patientsOver20MinsIds = $patientsOver20MinsQuery->pluck('patient_id');


            $patients = User::with([
                'ccdProblems' => function ($query) {
                    $query->whereNotNull('cpm_problem_id');
                },
                'cpmProblems',
                'patientInfo',
            ])
                ->whereHas('roles', function ($q) use
                (
                    $patientRole
                ) {
                    $q->where('name', '=', $patientRole->name);
                })
                ->whereHas('patientInfo', function ($query) use
                (
                    $ccmStatuses
                ) {
                    $query->whereIn('ccm_status', $ccmStatuses);
                })
                ->where('program_id', '=', $programId)
                ->whereIn('id', $patientsOver20MinsIds)
                ->get();

            $problems = [];

            foreach ($patients as $patient) {

                $billableCpmProblems = [];

                $calls = Call::where(function ($q) use
                (
                    $patient
                ) {
                    $q->where('inbound_cpm_id', $patient->id)
                        ->orWhere('outbound_cpm_id', $patient->id);
                })
                    ->whereStatus('reached')
                    ->whereBetween('updated_at', [
                        $start,
                        $end,
                    ])
                    ->get();

                $provider = User::find($patient->billingProviderID);

                if (!$provider) {
                    continue;
                }

                //for patients whose ccd problems were not logged
                //we're gonna pick their cpm problems
                if (count($patient->ccdProblems) < 1) {

                    $patientCpmProblems = $patient->cpmProblems;

                    //get the other conditions misc. we need its id
                    $cpmMisc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)->first();


                    $cpmMiscOtherCond = $patient->cpmMiscs()->wherePivot('cpm_misc_id', $cpmMisc->id)->first();

                    //if there's no instruction return N/A
                    //mainly done because we're exporting to a spreadsheet
                    if (empty($cpmMiscOtherCond->pivot->cpm_instruction_id)) {
                        $instruction = false;
                    } else {
                        $instruction = CpmInstruction::find($cpmMiscOtherCond->pivot->cpm_instruction_id);
                    }

                    //now we are gonna try and pattern match the problem's name
                    //to a line in the other conditions field.
                    //may god be with us on this quest
                    foreach ($patientCpmProblems as $cpmProblem) {

                        if (empty($instruction)) {
                            //add it to billable
                            $billableCpmProblems[] = $cpmProblem;
                            $otherConditionsText[$cpmProblem->id] = 'N/A';

                            continue;
                        }

                        //check for the keywords
                        $keywords = explode(',', $cpmProblem->contains);

                        foreach ($keywords as $keyword) {
                            if (empty($keyword)) {
                                continue;
                            }

                            if ($strPos = strpos($instruction->name, $keyword)) {

                                $break = strpos($instruction->name, ';', $strPos);

                                $otherConditionsText[$cpmProblem->id] = substr($instruction->name, $strPos,
                                    $break - $strPos);

                                //add it to billable
                                $billableCpmProblems[] = $cpmProblem;

                                continue 2;
                            }
                        }

                        //search blindly using everything from the snomed table
                        //put this in a function later

                        $keywords = SnomedToICD10Map::where('icd_10_code', 'like', "%$cpmProblem->icd10from%")
                            ->pluck('icd_10_name');

                        foreach ($keywords as $keyword) {
                            if (empty($keyword)) {
                                continue;
                            }

                            if ($strPos = strpos($instruction->name, $keyword)) {

                                $break = strpos($instruction->name, ';', $strPos);

                                $otherConditionsText[$cpmProblem->id] = substr($instruction->name, $strPos,
                                    $break - $strPos);

                                //add it to billable
                                $billableCpmProblems[] = $cpmProblem;

                                continue 2;
                            }
                        }

                        $billableCpmProblems[] = $cpmProblem;
                    }

                    //why didn't I just loop here?
                    $billableCpmProblems[0] = isset($billableCpmProblems[0])
                        ? $billableCpmProblems[0]
                        : new CpmProblem();

                    $billableCpmProblems[1] = isset($billableCpmProblems[1])
                        ? $billableCpmProblems[1]
                        : new CpmProblem();

                    $message = is_object($instruction)
                        ? $instruction->name
                        : 'N/A';

                    $problems[] = [
                        'provider_name' => isset($provider)
                            ? $provider->fullName
                            : 'N/A',
                        'patient_name'  => $patient->fullName,
                        'patient_dob'   => $patient->birthDate,
                        'patient_phone' => $patient->primaryPhone,

                        'problem_name_1'          => $billableCpmProblems[0]->name,
                        'problem_code_1'          => 'N/A',
                        'code_system_name_1'      => 'N/A',
                        'other_conditions_text_1' => isset($otherConditionsText[$billableCpmProblems[0]->id])
                            ? $otherConditionsText[$billableCpmProblems[0]->id]
                            //otherwise just output the whole instruction
                            : $message,

                        'problem_name_2'          => $billableCpmProblems[1]->name,
                        'problem_code_2'          => 'N/A',
                        'code_system_name_2'      => 'N/A',
                        'other_conditions_text_2' => isset($otherConditionsText[$billableCpmProblems[1]->id])
                            ? $otherConditionsText[$billableCpmProblems[1]->id]
                            //otherwise just output the whole instruction
                            : $message,

                        'ccm_status' => $patient->patientInfo->ccm_status,

                        'ccm_time' => number_format($patientsOver20Mins->get($patient->id)->ccmTime / 60, 2),

                        '#_succ_clin_calls' => $calls->count(),
                    ];

                    continue;
                }


                $patientCcdProblems = $patient->ccdProblems;

                $billableCcdProblems[0] = isset($patientCcdProblems[0])
                    ? $patientCcdProblems[0]
                    : new Problem();

                $billableCcdProblems[1] = isset($patientCcdProblems[1])
                    ? $patientCcdProblems[1]
                    : new Problem();

                array_push($problems, [
                    'provider_name' => isset($provider)
                        ? $provider->fullName
                        : 'N/A',
                    'patient_name'  => $patient->fullName,
                    'patient_dob'   => $patient->birthDate,
                    'patient_phone' => $patient->primaryPhone,

                    'problem_name_1'          => $billableCcdProblems[0]->name,
                    'problem_code_1'          => $billableCcdProblems[0]->code,
                    'code_system_name_1'      => $billableCcdProblems[0]->code_system_name,
                    'other_conditions_text_1' => 'N/A',

                    'problem_name_2'          => $billableCcdProblems[1]->name,
                    'problem_code_2'          => $billableCcdProblems[1]->code,
                    'code_system_name_2'      => $billableCcdProblems[1]->code_system_name,
                    'other_conditions_text_2' => 'N/A',

                    'ccm_status' => $patient->patientInfo->ccm_status,

                    'ccm_time' => number_format($patientsOver20Mins->get($patient->id)->ccmTime / 60, 2),

                    '#_succ_clin_calls' => $calls->count(),
                ]);
            }

            $direction = $under
                ? 'under'
                : 'over';

            $worksheets[] = compact([
                'problems',
                'program',
                'month',
                'year',
            ]);

        }

        Excel::create("Billing Report $direction $ccmTimeMin minutes - $month/$year", function ($excel) use
        (
            $worksheets
        ) {

            //Add program to each patient for master list
            $masterList = [];
            foreach ($worksheets as $worksheet) {
                foreach ($worksheet['problems'] as $row) {
                    $row['program'] = $worksheet['program']->display_name;
                    $masterList[] = $row;
                }
            }

            $excel->sheet('Master', function ($sheet) use
            (
                $masterList
            ) {
                $sheet->fromArray(
                    $masterList
                );
            });
            foreach ($worksheets as $worksheet) {

                $sheetName = $worksheet['program']->name;

                if (strlen($sheetName) > 31) {
                    $sheetName = substr($sheetName, 0, 31);
                }

                $excel->sheet("$sheetName", function ($sheet) use
                (
                    $worksheet
                ) {
                    $sheet->fromArray(
                        $worksheet['problems']
                    );
                });
            }
        })->export('xls');

    }

    public function make()
    {

        $practices = Practice::all();

        return view('admin.reports.billing', compact(['practices']));

    }

    public function data(Request $request)
    {
        $input = $request->input();


        //Carbon::now()->firstOfMonth()->toDateString()
        $patients =
            Patient::whereHas('patientSummaries', function ($q) {
                $q->where('ccm_time', '>', 1199)
                    ->where('month_year', '2017-02-01');

            });

        if ($input['practice_id'] != 0) {

            $practice = $input['practice_id'];

            $patients = $patients->whereHas('user', function ($k) use
            (
                $practice

            ) {
                $k->whereProgramId($practice);
            });
        }


        $patients = $patients->orderBy('updated_at', 'desc')
            ->take(100)
            ->pluck('user_id');

        $count = 0;
        $formatted = [];


        foreach ($patients as $p) {

            $u = User::find($p);
            $info = $u->patientInfo;

            //@todo add problem type and code
            $problems = $u->cpmProblems()->take(2)->pluck('name');

            $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
            $report = PatientMonthlySummary::where('patient_info_id', $info->id)->where('month_year',
                $day_start)->first();

            if ($report != null) {
                $checked = ($report->approved == 1)
                    ? 'checked'
                    : '';
            } else {
                $checked = '';
            }

            if (!isset($problems[0])) {
                $problems[0] = 'N/A';
                $checked = '';
            }

            if (!isset($problems[1])) {
                $problems[1] = 'N/A';
                $checked = '';
            }

            $name = "<a href=" . URL::route('patient.summary', ['patient' => $u->id]) . "> " . $u->fullName . "</a>";

            $formatted[$count] = [

                'name'     => $name,
                'provider' => $u->billingProvider()->fullName,
                'practice' => $u->primaryPractice->display_name,
                'dob'      => $info->birth_date,
                'ccm'      => round($info->cur_month_activity_time / 60, 2),
                'problem1' => $problems[0],
                'problem2' => $problems[1],
                'status'   => $info->ccm_status,
                'approve'  => "<input type=\"checkbox\" id='$u->id' $checked>",

            ];
            $count++;

        }

        $formatted = collect($formatted);


        return Datatables::of($formatted)
            ->addColumn('background_color', function ($a) {
                if ($a['problem1'] == 'N/A' || $a['problem2'] == 'N/A' || $a['status'] == 'withdrawn' || $a['status'] == 'paused') {
                    return 'rgba(255, 252, 96, 0.407843)';
                } else {
                    return '';
                }
            })
            ->make(true);

    }
}
