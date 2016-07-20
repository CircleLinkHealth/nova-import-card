<?php

namespace App\Http\Controllers;

use App\Call;
use App\Http\Requests;
use App\Services\Calls\SchedulerService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CallController extends Controller
{

    private $scheduler;

    public function __construct(SchedulerService $callScheduler)
    {
        $this->scheduler = $callScheduler;
    }

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

        $patient = User::find($input['patient_id'])->patientInfo();

        //temp add 1 hour to make window
        $window_end = Carbon::parse($input['time'])->addHour()->format('H:i:s');

        $this->scheduler->storeScheduledCall($input['patient_id'], $window_start, $input['date']);

        return redirect()->route('patient.note.index', ['patient' => $input['patient_id']])->with('messages', ['Successfully Created Note']);
        
    }

    public function show($id)
    {
        //
    }

}
