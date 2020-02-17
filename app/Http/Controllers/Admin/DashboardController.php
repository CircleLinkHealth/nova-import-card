<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
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
            return view('admin.dashboard', compact(['user']));
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

    public function upg0506($type)
    {
        if (isProductionEnv()) {
            return redirect()->back();
        }

        Artisan::call('demo:upg0506', ["--{$type}" => true]);

        return redirect()->back();
    }
}
