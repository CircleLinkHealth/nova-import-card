<?php

namespace App\Http\Controllers;

use App\Practice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportsController extends Controller
{

    //PROVIDER REPORTS

    public function createProviderReport(Request $request)
    {

        $programs = Practice::all()->pluck('display_name', 'id');

        $sections = [
            'Enrollment Summary',
            'Financial Summary'
        ];

        return view('sales.create',
            [

                'programs' => $programs,
                'sections' => $sections

            ]);

    }



    public function makeLocationReport(Request $request)
    {

        $input = $request->all();

        $programs = $input['programs'];

        $withHistory = isset($input['withPastMonth'])
            ? true
            : false;

        $links = [];

        foreach ($programs as $program) {

            $program = Practice::find($program);

            $links[$program->display_name] = (new SalesByLocationReport($program,
                Carbon::parse($input['start_date']),
                Carbon::parse($input['end_date']),
                $withHistory)
            )->handle();
        }

        return view('sales.reportlist', ['reports' => $links]);

    }

    //LOCATION REPORTS

    public function createLocationReport(Request $request)
    {

        $programs = Practice::all()->pluck('display_name', 'id');

        $sections = [
            'Enrollment Summary',
            'Financial Summary'
        ];

        return view('sales.create',
            [

                'programs' => $programs,
                'sections' => $sections

            ]);

    }



    public function makeLocations(Request $request)
    {

        $input = $request->all();

        $programs = $input['programs'];

        $withHistory = isset($input['withPastMonth'])
            ? true
            : false;

        $links = [];

        foreach ($programs as $program) {

            $program = Practice::find($program);

            $links[$program->display_name] = (new SalesByLocationReport($program,
                Carbon::parse($input['start_date']),
                Carbon::parse($input['end_date']),
                $withHistory)
            )->handle();
        }

        return view('sales.reportlist', ['reports' => $links]);

    }

}
