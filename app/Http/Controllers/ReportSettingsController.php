<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReportSettings;
use Illuminate\Support\Facades\DB;

class ReportSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = DB::table('report_settings')->get();

        $nurseSuccessful   = $settings->where('name', 'nurse_report_successful')->first();
        $nurseUnsuccessful = $settings->where('name', 'nurse_report_unsuccessful')->first();
        $timeGoal          = $settings->where('name', 'time_goal_per_billable_patient')->first();

        return view('admin.report-settings', compact([
            'nurseSuccessful',
            'nurseUnsuccessful',
            'timeGoal',
        ]));
    }

    public function update(UpdateReportSettings $request)
    {
        DB::table('report_settings')
            ->where('name', 'nurse_report_successful')
            ->update(['value' => $request->get('nurse_successful')]);

        DB::table('report_settings')
            ->where('name', 'nurse_report_unsuccessful')
            ->update(['value' => $request->get('nurse_unsuccessful')]);

        DB::table('report_settings')
            ->where('name', 'time_goal_per_billable_patient')
            ->update(['value' => $request->get('time_goal')]);

        return redirect()->back();
    }
}
