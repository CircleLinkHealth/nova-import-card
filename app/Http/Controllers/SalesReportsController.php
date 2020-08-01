<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Mail\SalesPracticeReport;
use App\Reports\Sales\Location\SalesByLocationReport;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SalesReportsController extends Controller
{
    /**
     * @var PdfService
     */
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function createLocationReport(
        Request $request
    ) {
        $programs = Practice::all()->pluck('display_name', 'id');

        $sections = [
            'Enrollment Summary',
            'Financial Summary',
        ];

        return view(
            'sales.by-location.create',
            [
                'programs' => $programs,
                'sections' => $sections,
            ]
        );
    }

    //PRACTICE REPORTS

    public function createPracticeReport(Request $request)
    {
        $practices = Practice::active()->get()->pluck('display_name', 'id');

        $sections = SalesByPracticeReport::SECTIONS;

        return view(
            'sales.by-practice.create',
            [
                'sections'  => $sections,
                'practices' => $practices,
            ]
        );
    }

    //PROVIDER REPORTS

    public function createProviderReport(Request $request)
    {
        $providers = User::ofType('provider')->get()->sortBy('display_name')->pluck('display_name', 'id');

        $sections = SalesByProviderReport::SECTIONS;

        return view(
            'sales.by-provider.create',
            [
                'sections'  => $sections,
                'providers' => $providers,
            ]
        );
    }

    public function makeLocationReport(
        Request $request
    ) {
        $input = $request->all();

        $programs = $input['programs'];

        $withHistory = isset($input['withPastMonth'])
            ? true
            : false;

        $links = [];

        foreach ($programs as $program) {
            $program = Practice::find($program);

            $links[$program->display_name] = (new SalesByLocationReport(
                $program,
                Carbon::parse($input['start_date']),
                Carbon::parse($input['end_date']),
                $withHistory
            )
            )->handle();
        }

        return view('sales.reportlist', ['reports' => $links]);
    }

    public function makePracticeReport(Request $request)
    {
        $input = $request->all();

        $sections = $input['sections'];
        $practice = Practice::find($input['practice']);

        $data = (new SalesByPracticeReport(
            $practice,
            $sections,
            Carbon::parse($input['start_date']),
            Carbon::parse($input['end_date'])
        ))
            ->data();

        $data['name']    = $practice->display_name;
        $data['start']   = Carbon::parse($input['start_date']);
        $data['end']     = Carbon::parse($input['end_date']);
        $data['isEmail'] = false;

        if ('test' == $input['submit']) {
            $subjectPractice = $practice->display_name.'\'s CCM Weekly Summary';

            $practiceData['isEmail'] = true;

            $email = $input['email'];

            Mail::send(new SalesPracticeReport($practice, $data, $email));

            return 'Sent to '.$input['email'].'!';
        }

        //PDF download support
        if ('download' == $input['submit']) {
            $name = $practice->display_name.'-'.Carbon::now()->toDateString();
            $path = storage_path("download/${name}.pdf");

            $pdf = $this->pdfService->createPdfFromView('sales.by-practice.report', ['data' => $data], $path);

            return response()->download($path, $name, ['Content-Length: '.filesize($path)]);
        }

        return view('sales.by-practice.report', ['data' => $data]);
    }

    public function makeProviderReport(Request $request)
    {
        $input = $request->all();

        $provider = User::find($input['provider']);
        $sections = $input['sections'];

        $data = (new SalesByProviderReport(
            $provider,
            $sections,
            Carbon::parse($input['start_date']),
            Carbon::parse($input['end_date'])
        ))
            ->data();

        $data['name']    = $provider->getFullName();
        $data['start']   = Carbon::parse($input['start_date']);
        $data['end']     = Carbon::parse($input['end_date']);
        $data['isEmail'] = false;

        //PDF download support
        if ('download' == $input['submit']) {
            $name = $provider->getLastName().'-'.Carbon::now()->toDateString();
            $path = storage_path("download/${name}.pdf");

            $pdf = $this->pdfService->createPdfFromView('sales.by-provider.report', ['data' => $data], $path);

            return response()->download($path, $name, [
                'Content-Length: '.filesize($path),
            ]);
        }

        return view('sales.by-provider.report', ['data' => $data]);
    }
}
