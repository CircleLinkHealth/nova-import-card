<?php

namespace App\Http\Controllers;

use App\Call;
use App\Patient;
use App\Services\Calls\SchedulerService;
use App\User;
use App\Http\Resources\Call as CallResource;
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

        $calls = Call::where(function ($q) {
            $q->whereNull('type')
              ->orWhere('type', '=', 'call');
        })->where('status', 'scheduled')->get();

        return $calls;
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $call  = $this->createCall($input);
        if ( ! isset($call['errors'])) {
            return response()
                ->json($call, 201);
        } else {
            return response()
                ->json($call, $call['code']);
        }
    }

    public function createMulti(Request $request)
    {
        //input should be an array of calls to be created
        $input = $request->all();

        $result = [];
        foreach ($input as $item) {
            $result[] = $this->createCall($item);
        }
        return response()
            ->json($result, 201);
    }

    /**
     * @param $input
     *
     * @return array|static
     */
    private function createCall($input)
    {
        $validation = \Validator::make($input, [
            'type'            => 'required',
            'sub_type'        => '',
            'inbound_cpm_id'  => 'required',
            'outbound_cpm_id' => '',
            'scheduled_date'  => 'required|date',
            'window_start'    => 'required|date_format:H:i',
            'window_end'      => 'required|date_format:H:i',
            'attempt_note'    => '',
            'is_manual'       => 'required|boolean',
            'family_override' => '',
        ]);

        if ($validation->fails()) {
            return [
                'errors' => $validation->errors()->getMessages(),
                'code'   => 422,
            ];
        }

        // validate patient doesnt already have a scheduled call
        $patient = User::find($input['inbound_cpm_id']);
        if ( ! $patient) {
            return [
                'errors' => ['could not find patient'],
                'code'   => 406,
            ];
        }

        if ($input['type'] === 'call' && $patient->inboundCalls) {
            $scheduledCall = $patient->inboundCalls()
                                     ->where(function ($q) {
                                         $q->whereNull('type')
                                           ->orWhere('type', '=', 'call');
                                     })
                                     ->where('status', '=', 'scheduled')
                                     ->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'))
                                     ->first();
            if ($scheduledCall) {
                return [
                    'errors' => ['patient already has a scheduled call'],
                    'code'   => 406,
                ];
            }
        }

        $isFamilyOverride = ! empty($input['family_override']);
        if ( ! $isFamilyOverride
             && $this->hasAlreadyFamilyCallAtDifferentTime($patient->patientInfo, $input['scheduled_date'],
                $input['window_start'], $input['window_end'])) {

            return [
                'errors' => ['patient belongs to family and the family has a call at different time'],
                'code'   => 418,
            ];
        }

        $call = $this->storeNewCall($patient, $input);

        if ($input['type'] === 'call') {
            $this->storeNewCallForFamilyMembers($patient, $input);
        }

        return CallResource::make($call);
    }

    private function storeNewCallForFamilyMembers(User $patient, $input)
    {

        if ( ! $patient->patientInfo->hasFamilyId()) {
            return;
        }

        $familyMembers = $patient->patientInfo->getFamilyMembers($patient->patientInfo);
        if ( ! empty($familyMembers)) {
            foreach ($familyMembers as $familyMember) {
                $familyMemberCall = $this->scheduler->getScheduledCallForPatient($familyMember->user);
                if ($familyMemberCall) {

                    //be extra safe here. if we have a manual call just skip this patient
                    if ($familyMemberCall->is_manual) {
                        continue;
                    }

                    //cancel this call
                    $familyMemberCall->status = 'rescheduled/family';
                    $familyMemberCall->save();

                }
                $this->storeNewCall($familyMember->user, $input);
            }
        }
    }

    /**
     * @param User $user
     * @param $input
     *
     * @return Call
     */
    private function storeNewCall(User $user, $input)
    {
        $isFamilyOverride = ! empty($input['family_override']);

        $call                  = new Call;
        $call->type            = $input['type'];
        $call->sub_type        = isset($input['sub_type']) ? $input['sub_type'] : null;
        $call->inbound_cpm_id  = $user->id;
        $call->scheduled_date  = $input['scheduled_date'];
        $call->window_start    = $input['window_start'];
        $call->window_end      = $input['window_end'];
        $call->attempt_note    = $input['attempt_note'];
        $call->note_id         = null;
        $call->is_cpm_outbound = 1;
        $call->service         = 'phone';
        $call->status          = 'scheduled';
        $call->scheduler       = auth()->user()->id;
        $call->is_manual       = boolval($input['is_manual']) || $isFamilyOverride;

        if (empty($input['outbound_cpm_id'])) {
            $call->outbound_cpm_id = null;
        } else {
            $call->outbound_cpm_id = $input['outbound_cpm_id'];
        }

        $call->save();
        return $call;
    }

    /**
     * This handler is only used by nurses, so calls scheduled from here
     * have is_manual = true
     *
     * @param Request $request
     * @param $patientId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function schedule(Request $request, $patientId)
    {
        $input = $request->all();

        $window_start = Carbon::parse($input['window_start'])->format('H:i');
        $window_end   = Carbon::parse($input['window_end'])->format('H:i');

        //If the suggested date doesn't match the one in the input,
        //the scheduler has changed the date, mark it.
        $scheduler = ($input['suggested_date'] == $input['date'])
            ? 'core algorithm'
            : Auth::user()->id;

        $is_manual = $scheduler !== 'core algorithm';

        //We are storing the current caller as the next scheduled call's outbound cpm_id
        $this->scheduler->storeScheduledCall(
            $patientId,
            $window_start,
            $window_end,
            $input['date'],
            $scheduler,
            $input['nurse'],
            isset($input['attempt_note'])
                ? $input['attempt_note']
                : '',
            $is_manual
        );

        //not used ??
        //$patient = Patient::where('user_id', intval($patientId))->first();

        return redirect()->route('patient.note.index', [
            'patientId' => $patientId,
        ])
                         ->with('messages', ['Successfully Created Note']);
    }

    public function show($id)
    {
        //
    }

    public function showCallsForPatient($patientId)
    {
        $calls = Call::where(function ($q) {
            $q->whereNull('type')
              ->orWhere('type', '=', 'call');
        })->where('inbound_cpm_id', $patientId)->paginate();

        return view('admin.calls.index', ['calls' => $calls, 'patient' => User::find($patientId)]);
    }

    public function getPatientNextScheduledCallJson($patientId)
    {
        return response()->json(SchedulerService::getNextScheduledCall($patientId));
    }

    public function update(Request $request)
    {
        $data = $request->only(
            'callId',
            'columnName',
            'value',
            'familyOverride'
        );

        $columnsToCheckForOverride = ['scheduled_date', 'window_start', 'window_end'];
        $isFamilyOverride          = ! empty($data['familyOverride']);

        // VALIDATION
        if (empty($data['callId'])) {
            return response("missing required params", 401);
        }
        if ( ! Auth::user()) {
            return response("missing required scheduler user", 401);
        }

        // find call
        $call = Call::find($data['callId']);
        if ( ! $call) {
            return response("could not locate call " . $data['callId'], 401);
        }

        $col   = $data['columnName'];
        $value = $data['value'];

        if (in_array($col, $columnsToCheckForOverride)
            && ! $isFamilyOverride
            && $call->inboundUser
            && $call->inboundUser->patientInfo) {

            $mustConfirm = false;
            switch ($col) {
                case 'scheduled_date':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime($call->inboundUser->patientInfo, $value,
                        $call->window_start, $call->window_end);
                    break;
                case 'window_start':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime($call->inboundUser->patientInfo,
                        $call->scheduled_date, $value, $call->window_end);
                    break;
                case 'window_end':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime($call->inboundUser->patientInfo,
                        $call->scheduled_date, $call->window_start, $value);
                    break;
            }

            if ($mustConfirm) {
                return response(
                    'patient belongs to family and the family has a call at different time',
                    418);
            }
        }


        if ($isFamilyOverride) {
            $call->is_manual = true;
        }

        // for null outbound_cpm_id
        if ($col == 'outbound_cpm_id' && (empty($value) || strtolower($value) == 'unassigned')) {
            $call->scheduler = Auth::user()->id;
            $call->$col      = null;
        } else if ($col == 'attempt_note' && (empty($value) || strtolower($value) == 'add text')) {
            $call->attempt_note = '';
        } else if ($col == 'general_comment') {
            if ((empty($value) || strtolower($value) == 'add text')) {
                $value = '';
            }
            if ($call->inboundUser && $call->inboundUser->patientInfo) {
                $call->inboundUser->patientInfo->general_comment = $value;
                $call->inboundUser->patientInfo->save();
            }
        } else {
            $call->$col = $value;

            // CPM-149 - assigning a nurse to the call should not change scheduler,
            // so we will know if it was a nurse that originally scheduled this call
            if ($col != 'outbound_cpm_id') {
                $call->scheduler = Auth::id();
            }

        }

        $call->save();

        return response(
            "successfully updated call " . $data['columnName'] . "=" . $data['value'] . " - CallId=" . $data['callId'],
            201
        );
    }

    private function hasAlreadyFamilyCallAtDifferentTime(Patient $patient, $scheduledDate, $windowStart, $windowEnd)
    {
        $mustConfirm = false;

        if ( ! $patient->hasFamilyId()) {
            return $mustConfirm;
        }

        //now find if a another call is scheduled for any of the members of the family
        $familyMembers = $patient->getFamilyMembers($patient);
        if ( ! empty($familyMembers)) {
            foreach ($familyMembers as $familyMember) {
                $callForMember = $this->scheduler->getScheduledCallForPatient($familyMember->user);
                if ( ! $callForMember) {
                    continue;
                }

                if ($callForMember->scheduled_date != $scheduledDate
                    || $callForMember->window_start != $windowStart
                    || $callForMember->window_end != $windowEnd) {

                    $mustConfirm = true;
                    //no need to check other calls, so we break
                    break;
                }

            }
        }

        return $mustConfirm;
    }

    public function import(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            $failed = $this->scheduler->importCallsFromCsv($csv);

            echo "Failed to schedule a call for these patients:" . PHP_EOL;

            foreach ($failed as $fail) {
                echo "Name: $fail" . PHP_EOL;
            }
        }
    }

    /**
     * Cancel a call and create a new one (setting the status to 'rescheduled/cancelled').
     * If no call exists, just create the new one.
     * If called from a care-center role, the outbound_cpm_id is
     * set to the caller's user id.
     * If called from any other role, outbound_cpm_id must be provided.
     */
    public function reschedule(Request $request)
    {
        $input = $request->only(
            'id',
            'outbound_cpm_id'
        );

        //if no outbound id is set, we user the authenticated user's id
        //we do not have outbound_cpm_id when we are not sending a call to reschedule/cancel
        //and we are just creating a new one.
        if (empty($input['outbound_cpm_id'])) {

            $user = Auth::user();
            if ($user->hasRole('care-center')) {
                $request->merge(['outbound_cpm_id' => auth()->user()->id]);
            } else {
                return response("missing outbound_cpm_id", 402);
            }
        }

        if ( ! empty($input['id'])) {
            $previousCall = Call::find($input['id']);
            if ( ! $previousCall) {
                return response("could not locate call " . $input['id'], 401);
            }

            $previousCall->status = 'rescheduled/cancelled';
            $previousCall->save();
        }

        return $this->create($request);
    }
}
