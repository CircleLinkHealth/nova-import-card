<?php

namespace App\Http\Controllers;

use App\Call;
use App\PatientInfo;
use App\Services\Calls\SchedulerService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{

    private $scheduler;

    public function __construct(SchedulerService $callScheduler)
    {
        $this->scheduler = $callScheduler;
    }

    public function index(Request $request)
    {
        
        $calls = Call::where('status','scheduled')->get();

    return $calls;

    }

    public function create(Request $request)
    {
        $validation = \Validator::make( $request->all(), [
            'inbound_cpm_id' => 'required',
            'outbound_cpm_id' => '',
            'scheduled_date' => 'required|date',
            'window_start' => 'required|date_format:H:i',
            'window_end' => 'required|date_format:H:i',
            'attempt_note' => ''
        ]);

        if( $validation->fails() )
        {
            return response(json_encode([
                'errors' => $validation->errors()->getMessages(),
                'code' => 422
            ]), 422);
        }

        $input = $request->only('inbound_cpm_id',
            'outbound_cpm_id',
            'scheduled_date',
            'window_start',
            'window_end',
            'attempt_note');

        // validate patient doesnt already have a scheduled call
        $patient = User::find($input['inbound_cpm_id']);
        if(!$patient) {
            return response(json_encode([
                'errors' => ['could not find patient'],
                'code' => 406
            ]), 406);
        }

        if($patient->inboundCalls) {
            $scheduledCall = $patient->inboundCalls()->where('status', '=', 'scheduled')->first();
            if($scheduledCall) {
                return response(json_encode([
                    'errors' => ['patient already has a scheduled call'],
                    'code' => 406
                ]), 406);
            }
        }

        $call = new Call;
        $call->inbound_cpm_id = $input['inbound_cpm_id'];
        if(empty($input['outbound_cpm_id'])) {
            $call->outbound_cpm_id = null;
        } else {
            $call->outbound_cpm_id = $input['outbound_cpm_id'];
        }
        $call->scheduled_date = $input['scheduled_date'];
        $call->window_start = $input['window_start'];
        $call->window_end = $input['window_end'];
        $call->attempt_note = $input['attempt_note'];
        $call->note_id = null;
        $call->is_cpm_outbound = 1;
        $call->service = 'phone';
        $call->status = 'scheduled';
        $call->scheduler = auth()->user()->id;
        $call->save();

        return response("successfully created call ", 201);
        //return view('wpUsers.patient.calls.create');
    }

    public function schedule(Request $request)
    {
        $input = $request->all();

        $window_start = Carbon::parse($input['window_start'])->format('H:i');
        $window_end = Carbon::parse($input['window_end'])->format('H:i');

        $scheduler = ($input['suggested_date'] == $input['date'])
            ? 'core algorithm'
            : Auth::user()->id;


//        dd([
//            $input['patient_id'],
//            $window_start,
//            $window_end,
//            $input['date'],
//            $scheduler,
//            $input['nurse'] ? $input['nurse'] : null,
//            isset($input['attempt_note']) ? $input['attempt_note'] : ''
//        ]);

        //We are storing the current caller as the next scheduled call's outbound cpm_id
        $this->scheduler->storeScheduledCall(
                                                $input['patient_id'],
                                                $window_start,
                                                $window_end,
                                                $input['date'],
                                                $scheduler,
                                                $input['nurse'],
                                                isset($input['attempt_note']) ? $input['attempt_note'] : ''

                                            );


        $patient = PatientInfo::where('user_id', intval($input['patient_id']))->first();

        return redirect()->route('patient.note.index', ['patient' => $input['patient_id']])
                         ->with('messages', ['Successfully Created Note']);
        
    }

    public function show($id)
    {
        //
    }

    public function showCallsForPatient($patientId)
    {
        $calls = Call::where('inbound_cpm_id',$patientId)->paginate();

        return view('admin.calls.index', ['calls' => $calls, 'patient' => User::find($patientId)]);
    }

    public function update(Request $request)
    {

        $data = $request->only('callId',
            'columnName',
            'value');

        // VALIDATION
        if(empty($data['callId'])) {
            return response("missing required params", 401);
        }
        if(!Auth::user()) {
            return response("missing required scheduler user", 401);
        }

        // find call
        $call = Call::find($data['callId']);
        if(!$call) {
            return response("could not locate call ".$data['callId'], 401);
        }

        // for null outbound_cpm_id
        if($data['columnName'] == 'outbound_cpm_id' && (empty($data['value']) || strtolower($data['value']) == 'unassigned' )) {

            $call->scheduler = Auth::user()->id;
            $col = $data['columnName'];
            $call->$col = null;

        } else if($data['columnName'] == 'attempt_note' && (empty($data['value']) || strtolower($data['value']) == 'add text' )) {
            $call->attempt_note = '';
        } else if($data['columnName'] == 'general_comment') {
            $generalComment = $data['value'];
            if((empty($generalComment) || strtolower($generalComment) == 'add text' )) {
                $generalComment = '';
            }
            if($call->inboundUser && $call->inboundUser->patientInfo) {
                $call->inboundUser->patientInfo->general_comment = $generalComment;
                $call->inboundUser->patientInfo->save();
            }
        } else {
            $call->scheduler = Auth::user()->id;
            $col = $data['columnName'];
            $call->$col = $data['value'];
        }

        $call->save();

        return response("successfully updated call ".$data['columnName']."=".$data['value']." - CallId=".$data['callId'], 201);

    }

    public function import(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            $failed = $this->scheduler->importCallsFromCsv($csv);

            echo "Failed to schedule a call for these patients:" . PHP_EOL;

            foreach ($failed as $fail)
            {
                echo "Name: $fail" . PHP_EOL;
            }
        }
    }

}
