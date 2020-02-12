<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Charts\TotalBillablePatients;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();

        // switch dashboard view based on logged in user
        if ($user->hasRole(['administrator', 'administrator-view-only'])) {
            $chart = $this->getBillablePatientsChart();

            return view('admin.dashboard', compact(['user', 'chart']));
        }

        return redirect()->route('patients.dashboard', []);
    }

    public function pullAthenaEnrollees(Request $request)
    {
        $practice = Practice::find($request->input('practice_id'));

        $from = Carbon::parse($request->input('from'));
        $to   = Carbon::parse($request->input('to'));

        Artisan::call(
            'athena:autoPullEnrolleesFromAthena',
            [
                'athenaPracticeId' => $practice->external_id,
                'from'             => $from->format('y-m-d'),
                'to'               => $to->format('y-m-d'),
            ]
        );

        return redirect()->back()->with(['pullMsg' => 'Batch Created!']);
    }

    /**
     * Display the specified resource.
     *
     * @param int $patientId
     *
     * @return Response
     */
    public function testplan(Request $request)
    {
        $patient = User::find('393');

        return view('admin.testplan', compact(['patient']));
    }

    private function getBillablePatientsChart()
    {
        $clh = SaasAccount::whereSlug('circlelink-health')->first();

        if ( ! $clh) {
            return new TotalBillablePatients();
        }

        return Cache::remember(
            'chart:clh:total_billable_patients',
            90,
            function () use ($clh) {
                $period = CarbonPeriod::create(now()->subMonths(3), now());
                $collections = [];

                foreach ($period as $date) {
                    $collections[] = "ops-daily-report-{$date->toDateString()}.json";
                }

                $dataset = collect();

                Media::whereModelType(SaasAccount::class)->whereIn('collection_name', $collections)->orderByDesc(
                    'id'
                )->chunkById(
                    50,
                    function (Collection $medias) use (&$dataset) {
                        $medias->each(
                            function (Media $media) use (&$dataset) {
                                $json = $media->getFile();

                                //first check if we have a valid file
                                if ( ! $json) {
                                    return [];
                                }
                                //then check if it's in json format
                                if ( ! is_json($json)) {
                                    throw new \Exception('File retrieved is not in json format.', 500);
                                }

                                $decoded = json_decode($json, true);
                                $clhTotals = $decoded['rows']['CircleLink Total'] ?? [];

                                $dataset[] = [
                                    '20+'           => $clhTotals['20+'] + $clhTotals['20+ BHI'] ?? null,
                                    'total'         => $clhTotals['Total'] ?? null,
                                    'dateGenerated' => $decoded['dateGenerated'] ?? null,
                                ];
                            }
                        );
                    }
                );

                $chart = new TotalBillablePatients();
                $chart->labels($dataset->pluck('dateGenerated')->all());
                $chart->dataset('Patients 20+ Mins, Any Code', 'line', $dataset->pluck('20+')->all())->backgroundColor('#8aed00');
                $chart->dataset('Total Number Of Patients', 'line', $dataset->pluck('total')->all())->backgroundColor('#179553')->fill(false);

                return $chart;
            }
        );
    }
}
