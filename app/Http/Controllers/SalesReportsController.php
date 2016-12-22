<?php

namespace App\Http\Controllers;

use App\Practice;
use App\Reports\Sales\Provider\SalesByProviderReport;
use App\Reports\Sales\Location\SalesByLocationReport;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportsController extends Controller
{

    //PROVIDER REPORTS

    public function createProviderReport(Request $request)
    {

        $providers = User::ofType('provider')->get()->sortBy('display_name')->pluck('display_name', 'id');

        $sections = SalesByProviderReport::SECTIONS;

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
            ))
                ->data();
//                ->printData();
        }

        dd($links);

        return view('sales.reportlist', ['reports' => $links]);

    }

    public function createPracticeReport(Request $request)
    {

        // @TODO get practices.
        $practices = Practice::all()->pluck('display_name', 'id');

        $sections = SalesByPracticeReport::SECTIONS;

        return view('sales.by-practice.create',
            [

                'sections' => $sections,
                'practices' => $practices

            ]);

    }

    public function makePracticeReport(Request $request)
    {

        $input = $request->all();

        $practices = $input['practices'];
        $sections = $input['sections'];

        $links = [];

        foreach ($practices as $practice) {

            $provider = Practice::find($practice);

            $links[$provider->fullName] = (new SalesByPracticeReport
            (   $provider,
                $sections,
                Carbon::parse($input['start_date']),
                Carbon::parse($input['end_date'])
            ))
                ->data();
//                ->printData();
        }

        dd($links);

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

        return view('sales.by-location.create',
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

}
