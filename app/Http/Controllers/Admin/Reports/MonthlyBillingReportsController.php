<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Activity;
use App\Call;
use App\CLH\CCD\Importer\SnomedToICD10Map;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\CCD\CcdProblem;
use App\Models\CPM\CpmInstruction;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Note;
use App\Program;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyBillingReportsController extends Controller
{
    public function makeMonthlyReport(Request $request)
    {
        //whether over or under 20 minutes
        $under = $request->input('under', false);

        $overOrUnder = $under
            ? '<'
            : '>';

        //20 mins in seconds
        $ccmTimeMin = (20 * 60);
        $month = $request->input('month');
        $year = $request->input('year');
        $patientRole = Role::whereName('participant')->first();
        $programId = $request->input('programId');
        $program = Program::find($programId);


        $time = Carbon::createFromDate($year, $month, 15);
        $start = $time->startOfMonth()->startOfDay()->format("Y-m-d H:i:s");
        $end = $time->endOfMonth()->endOfDay()->format("Y-m-d H:i:s");

        $patientsOver20MinsQuery = DB::table('lv_activities')
            ->select(DB::raw('patient_id, SUM(duration) as ccmTime'))
            ->whereBetween('performed_at', [
                $start, $end
            ])
            ->having('ccmTime', $overOrUnder, $ccmTimeMin)
            ->groupBy('patient_id');

        $patientsOver20Mins = (new Collection($patientsOver20MinsQuery->get()))->keyBy('patient_id');
        $patientsOver20MinsIds = $patientsOver20MinsQuery->lists('patient_id');


        $patients = User::with([
            'ccdProblems' => function ($query) {
                $query->whereNotNull('cpm_problem_id');
            },
            'cpmProblems'
        ])
            ->whereHas('roles', function ($q) use ($patientRole) {
                $q->where('name', '=', $patientRole->name);
            })
            ->where('program_id', '=', $programId)
            ->whereIn('ID', $patientsOver20MinsIds)
            ->get();

        $problems = [];

        foreach ($patients as $patient) {

            $billableCpmProblems = [];

            $calls = Call::where(function ($q) use ($patient) {
                $q->where('inbound_cpm_id', $patient->ID)
                    ->orWhere('outbound_cpm_id', $patient->ID);
            })
                ->whereStatus('reached')
                ->whereBetween('created_at', [
                    $start, $end
                ])
                ->get();

            $provider = User::find($patient->billingProviderID);

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
                        if (empty($keyword)) continue;

                        if ($strPos = strpos($instruction->name, $keyword)) {

                            $break = strpos($instruction->name, ';', $strPos);

                            $otherConditionsText[$cpmProblem->id] = substr($instruction->name, $strPos, $break - $strPos);

                            //add it to billable
                            $billableCpmProblems[] = $cpmProblem;

                            continue 2;
                        }
                    }

                    //search blindly using everything from the snomed table
                    //put this in a function later

                    $keywords = SnomedToICD10Map::where('icd_10_code', 'like', "%$cpmProblem->icd10from%")
                        ->lists('icd_10_name');

                    foreach ($keywords as $keyword) {
                        if (empty($keyword)) continue;

                        if ($strPos = strpos($instruction->name, $keyword)) {

                            $break = strpos($instruction->name, ';', $strPos);

                            $otherConditionsText[$cpmProblem->id] = substr($instruction->name, $strPos, $break - $strPos);

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

                $message = is_object($instruction) ? $instruction->name : 'N/A';

                $problems[] = [
                    'provider_name' => $provider->fullName,
                    'patient_name' => $patient->fullName,
                    'patient_dob' => $patient->birthDate,
                    'patient_phone' => $patient->primaryPhone,

                    'problem_name_1' => $billableCpmProblems[0]->name,
                    'problem_code_1' => 'N/A',
                    'code_system_name_1' => 'N/A',
                    'other_conditions_text_1' => isset($otherConditionsText[$billableCpmProblems[0]->id])
                        ? $otherConditionsText[$billableCpmProblems[0]->id]
                        //otherwise just output the whole instruction
                        : $message,

                    'problem_name_2' => $billableCpmProblems[1]->name,
                    'problem_code_2' => 'N/A',
                    'code_system_name_2' => 'N/A',
                    'other_conditions_text_2' => isset($otherConditionsText[$billableCpmProblems[1]->id])
                        ? $otherConditionsText[$billableCpmProblems[1]->id]
                        //otherwise just output the whole instruction
                        : $message,

                    'ccm_time' => ceil($patientsOver20Mins->get($patient->ID)->ccmTime / 60),

                    '#_succ_clin_calls' => $calls->count(),
                ];

                continue;
            }


            $patientCcdProblems = $patient->ccdProblems;

            $billableCcdProblems[0] = isset($patientCcdProblems[0])
                ? $patientCcdProblems[0]
                : new CcdProblem();

            $billableCcdProblems[1] = isset($patientCcdProblems[1])
                ? $patientCcdProblems[1]
                : new CcdProblem();

            array_push($problems, [
                'provider_name' => $provider->fullName,
                'patient_name' => $patient->fullName,
                'patient_dob' => $patient->birthDate,
                'patient_phone' => $patient->primaryPhone,

                'problem_name_1' => $billableCcdProblems[0]->name,
                'problem_code_1' => $billableCcdProblems[0]->code,
                'code_system_name_1' => $billableCcdProblems[0]->code_system_name,
                'other_conditions_text_1' => 'N/A',

                'problem_name_2' => $billableCcdProblems[1]->name,
                'problem_code_2' => $billableCcdProblems[1]->code,
                'code_system_name_2' => $billableCcdProblems[1]->code_system_name,
                'other_conditions_text_2' => 'N/A',

                'ccm_time' => ceil($patientsOver20Mins->get($patient->ID)->ccmTime / 60),

                '#_succ_clin_calls' => $calls->count(),
            ]);
        }

        Excel::create("Billing Report - $month-$year - $program->display_name", function ($excel) use ($problems, $program, $month, $year) {
            $excel->sheet("Billing Report - $month-$year", function ($sheet) use ($problems) {
                $sheet->fromArray(
                    $problems
                );
            });
        })->export('csv');
    }
}
