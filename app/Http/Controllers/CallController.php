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

    public function index()
    {
        //
    }

    public function create()
    {
        return view('wpUsers.patient.calls.create');
    }

    public function schedule(Request $request)
    {

        $input = $request->all();

        dd($input);

        Call::create([

            'note_id' => '',
            'service' => 'phone',
            'status' => 'scheduled',

            'inbound_phone_number' => '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $input['patient_id'],
            'outbound_cpm_id' => '',
            
            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'call_date' => $input['date'],
            'window_start' => '',
            'window_start' => '',
            
            'is_cpm_outbound' => true

        ]);

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
