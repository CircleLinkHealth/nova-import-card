<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateNurseInvoice;
use App\Mail\NurseInvoiceMailer;
use App\Reports\NurseDailyReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Facades\Datatables;

class NurseController extends Controller
{
    public function makeInvoice()
    {

        $nurses = activeNurseNames();

        return view(
            'billing.nurse.create',
            [
                'nurses' => $nurses->sort(),
            ]
        );
    }

    public function generateInvoice(Request $request)
    {

        $input = $request->input();

        $nurseIds = $request->input('nurses');

        $addTime = $request->input('manual_time')
            ? $request->input('manual_time')
            : 0;

        $addNotes = $request->input('manual_time_notes')
            ? $request->input('manual_time_notes')
            : '';

        $variablePay = isset($input['alternative_pay']);

        if ($request->input('submit') == 'download') {
            $links = [];

            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));

            dispatch((new GenerateNurseInvoice($nurseIds, $startDate, $endDate, auth()->user()->id, $variablePay, $addTime, $addNotes)));
        }

        return "Waldo is working on compiling the reports you requested. <br> Give it a minute, and then head to " . link_to('/jobs/completed') . " and refresh frantically to see a link to the report you requested.";
    }

    public function sendInvoice(Request $request)
    {

        $invoices = (array)json_decode($request->input('links'));
        $month = $request->input('month');

        foreach ($invoices as $key => $value) {
            $value = (array)$value;

            $user = User::find($key);

            Mail::to($user)->send(new NurseInvoiceMailer($key, $value['link'], $month));
        }

        return redirect()->route('admin.reports.nurse.invoice')->with(['success' => 'yes']);
    }

    public function makeDailyReport()
    {

        return view('admin.reports.nursedaily');
    }

    public function dailyReport()
    {
        return Datatables::collection(NurseDailyReport::data())->make(true);
    }

    public function makeHourlyStatistics()
    {

//        $data = (new NurseCallStatistics(Nurse::all(),
//                                Carbon::parse('2016-09-29 09:00:00'),
//                                Carbon::parse('2016-09-29 10:00:00')))
//            ->nurseCallsPerHour();

        return view('statistics.nurses.info');
    }

    public function monthlyOverview(Request $request)
    {

        $input = $request->input();

        if (isset($input['next'])) {
            $dayCounter = Carbon::parse($input['next'])->firstOfMonth()->startOfDay();
            $last = Carbon::parse($input['next'])->lastOfMonth()->endOfDay();
        } elseif (isset($input['previous'])) {
            $dayCounter = Carbon::parse($input['previous'])->firstOfMonth()->startOfDay();
            $last = Carbon::parse($input['previous'])->lastOfMonth()->endOfDay();
        } else {
            $dayCounter = Carbon::now()->firstOfMonth()->startOfDay();
            $last = Carbon::now()->lastOfMonth()->endOfDay();
        }

        $nurses = User::ofType('care-center')->where('access_disabled', 0)->get();
        $data = [];


        while ($dayCounter->lte($last)) {
            foreach ($nurses as $nurse) {
                if (!$nurse->nurseInfo) {
                    continue;
                }

                $countScheduled = $nurse->nurseInfo->countScheduledCallsFor($dayCounter);

                $countMade = $nurse->nurseInfo->countCompletedCallsFor($dayCounter);

                $formattedDate = $dayCounter->format('m/d Y');

                $name = $nurse->first_name[0] . '. ' . $nurse->last_name;

                if ($countScheduled > 0) {
                    $data[$formattedDate][$name]['Scheduled'] = $countScheduled;
                } else {
                    $data[$formattedDate][$name]['Scheduled'] = 0;
                }

                if ($countMade > 0) {
                    $data[$formattedDate][$name]['Actual Made'] = $countMade;
                } else {
                    $data[$formattedDate][$name]['Actual Made'] = 0;
                }
            }

            $dayCounter = $dayCounter->addDays(1);
        }

        return view('admin.reports.allocation', [
            'data'  => $data,
            'month' => Carbon::parse($last),
        ]);
    }
}
