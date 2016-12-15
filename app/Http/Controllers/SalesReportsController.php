<?php

namespace App\Http\Controllers;

use App\Practice;
use App\Reports\Sales\SalesByProviderReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportsController extends Controller
{

    //PROVIDER REPORTS

    public function createProviderReport(Request $request)
    {

        $providers = User::ofType('provider')->get()->sortBy('display_name')->pluck('display_name', 'id');

        $sections = [
            'Overall Summary',
            'Enrollment Summary',
            'Financial Performance',
            'Practice Demographics'
        ];

        return view('sales.by-provider.create',
            [

                'sections' => $sections,
                'providers' => $providers

            ]);

    }

    public function makeProviderReport(Request $request)
    {

        $input = $request->all();

        $providers = $input['providers'];
        $sections = $input['sections'];

        $links = [];

        foreach ($providers as $provider) {

            $provider = User::find($provider);

            $links[$provider->fullName] = (new SalesByProviderReport
            (   $provider,
                $sections,
                Carbon::parse($input['start_date']),
                Carbon::parse($input['end_date'])
            ))->printData();
        }

        return view('sales.by-location.reportlist', ['reports' => $links]);

    }


    //LOCATION REPORTS

    public function createLocationReport(Request $request)
    {

        $programs = Practice::all()->pluck('display_name', 'id');

        $sections = [
            'Enrollment Summary',
            'Financial Summary'
        ];

        return view('sales.by-location.create',
            [

                'programs' => $programs,
                'sections' => $sections

            ]);

    }



    public function makeLocationsReport(Request $request)
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
