<?php

namespace App\Http\Controllers\API\Admin;

use App\Call;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CallsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $calls = Call::where('status', '=', 'scheduled')
            ->where('status', '=', 'scheduled')
            ->whereHas('inboundUser')
            ->with([
                'inboundUser.billingProvider.user'         => function ($q) {
                    $q->select(['id', 'first_name', 'last_name', 'suffix', 'display_name']);
                },
                'inboundUser.patientInfo.patientSummaries' => function ($q) {
                    $q->where('month_year', '=', Carbon::now()->format('Y-m-d'));
                },
                'inboundUser.primaryPractice',
                'outboundUser.nurseInfo',
                'note',
            ])
            ->paginate(50);

        return response()->json($calls);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
