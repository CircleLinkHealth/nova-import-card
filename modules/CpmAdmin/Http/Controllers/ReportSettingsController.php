<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\CpmAdmin\Http\Requests\UpdateReportSettings;
use CircleLinkHealth\SharedModels\Entities\ReportSetting;
use Illuminate\Routing\Controller;

class ReportSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = ReportSetting::get();

        $nurseSuccessful   = $settings->where('name', 'nurse_report_successful')->first();
        $nurseUnsuccessful = $settings->where('name', 'nurse_report_unsuccessful')->first();
        $timeGoal          = $settings->where('name', 'time_goal_per_billable_patient')->first();

        return view('cpm-admin::admin.report-settings', compact([
            'nurseSuccessful',
            'nurseUnsuccessful',
            'timeGoal',
        ]));
    }

    public function update(UpdateReportSettings $request)
    {
        ReportSetting::where('name', 'nurse_report_successful')
            ->update(['value' => $request->get('nurse_successful')]);

        ReportSetting::where('name', 'nurse_report_unsuccessful')
            ->update(['value' => $request->get('nurse_unsuccessful')]);

        ReportSetting::where('name', 'time_goal_per_billable_patient')
            ->update(['value' => $request->get('time_goal')]);

        return redirect()->back();
    }
}
