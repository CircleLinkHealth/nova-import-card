<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

    public function clearCache($key)
    {
        return response()->json([
            'cleared' => Cache::forget($key),
        ]);
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
            return view('cpm-admin::dashboard', compact(['user']));
        }

        return redirect()->route('patients.dashboard', []);
    }

    public function pullAthenaEnrollees(Request $request)
    {
        $practice = Practice::find($request->input('practice_id'));
        $batchId  = $request->input('batch_id');

        if ($batchId && ! EligibilityBatch::where('practice_id', $practice->id)->where('id', $batchId)->exists()) {
            return redirect()->back()->with(['pullMsg' => "Batch[$batchId] does not belong to {$practice->display_name}"]);
        }

        $from = Carbon::parse($request->input('from'));
        $to   = Carbon::parse($request->input('to'));

        Artisan::call(
            'athena:autoPullEnrolleesFromAthena',
            [
                'athenaPracticeId' => $practice->external_id,
                'from'             => $from->format('y-m-d'),
                'to'               => $to->format('y-m-d'),
                'offset'           => false,
                'batchId'          => $batchId,
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

        return view('cpm-admin::admin.testplan', compact(['patient']));
    }

    public function upg0506($type)
    {
        Artisan::call('demo:upg0506', ["--{$type}" => true]);

        if ('delete' == $type) {
            $message = 'Test data deleted successfully!';
        } else {
            $message = "Command to simulate receiving {$type} ran successfully!";
        }

        return redirect()->back()->with(['upg0506-command-success' => $message]);
    }
}
