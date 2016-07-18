<?php

namespace App\Http\Controllers;

use App\Call;
use App\Services\NoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CallController extends Controller
{

    public function index(Request $request)
    {

        $input = $request->all();
        
        $calls = Call::where('status','scheduled')->get();

    return $calls;

    }

    public function create()
    {
        return view('wpUsers.patient.calls.create');
    }

    public function schedule(Request $request)
    {

        $input = $request->all();

        $window_start = $input['time'];

        //temp add 1 hour to make window
        $window_end = Carbon::parse($input['time'])->addHour()->format('H:i:s');

        $call = Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'inbound_phone_number' => '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $input['patient_id'],
            'outbound_cpm_id' => 1,
            
            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'call_date' => $input['date'],
            'window_start' => $window_start,
            'window_end' => $window_end,

            'is_cpm_outbound' => true

        ]);

        return redirect()->route('patient.note.index', ['patient' => $input['patient_id']])->with('messages', ['Successfully Created Note']);

//        return redirect()->route('call.index', ['patientId' => $input['patient_id']]);

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
