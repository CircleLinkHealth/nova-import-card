<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Core\Rules\DateBeforeUsingCarbon;
use CircleLinkHealth\CpmAdmin\Http\Requests\CreateNewCallRequest;
use CircleLinkHealth\CpmAdmin\Http\Resources\Call as CallResource;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    private $scheduler;

    public function __construct(SchedulerService $callScheduler)
    {
        $this->scheduler = $callScheduler;
    }

    public function canChangeNursePatientRelation(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(CreateNewCallRequest $request)
    {
        $input = $request->all();
        $call  = $this->createCall($request->input());
        if ( ! isset($call['errors'])) {
            return response()
                ->json($call, 201);
        }

        return response()
            ->json($call, $call['code']);
    }

    public function createMulti(CreateNewCallRequest $request)
    {
        $input = $request->all();

        $result = [];
        foreach ($input as $item) {
            $result[] = $this->createCall($item);
        }

        return response()
            ->json($result, 201);
    }

    public function getPatientNextScheduledCallJson($patientId)
    {
        return response()->json(SchedulerService::getNextScheduledCall($patientId));
    }

    public function import(Request $request)
    {
        if ($request->hasFile('uploadedCsv')) {
            $csv = parseCsvToArray($request->file('uploadedCsv'));

            $failed = $this->scheduler->importCallsFromCsv($csv);

            echo 'Failed to schedule a call for these patients:'.PHP_EOL;

            foreach ($failed as $fail) {
                echo "Name: ${fail}".PHP_EOL;
            }
        }
    }

    public function index(Request $request)
    {
        $calls = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call');
        })->where('status', 'scheduled')->get();

        return $calls;
    }

    /**
     * Cancel a call and create a new one (setting the status to 'rescheduled/cancelled').
     * If no call exists, just create the new one.
     * If called from a care-center role, the outbound_cpm_id is
     * set to the caller's user id.
     * If called from any other role, outbound_cpm_id must be provided.
     */
    public function reschedule(CreateNewCallRequest $request)
    {
        $input = $request->only(
            'id',
            'outbound_cpm_id'
        );

        if (empty($input['outbound_cpm_id'])) {
            $user = Auth::user();
            if ($user->isCareCoach()) {
                $request->merge(['outbound_cpm_id' => auth()->user()->id]);
            } else {
                return response('missing outbound_cpm_id', 402);
            }
        }

        if ( ! empty($input['id'])) {
            $previousCall = Call::find($input['id']);
            if ( ! $previousCall) {
                return response('could not locate call '.$input['id'], 401);
            }

            $previousCall->status = 'rescheduled/cancelled';
            $previousCall->save();
        }

        $request->merge(['is_reschedule' => true]);

        return $this->create($request);
    }

    public function showCallsForPatient($patientId)
    {
        $calls = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call');
        })->where('inbound_cpm_id', $patientId)->paginate();

        return view('admin.calls.index', ['calls' => $calls, 'patient' => User::find($patientId)]);
    }

    public function update(Request $request)
    {
        $data = $request->only(
            'callId',
            'columnName',
            'value',
            'familyOverride',
        );

        $columnsToCheckForOverride = ['scheduled_date', 'window_start', 'window_end'];
        $isFamilyOverride          = ! empty($data['familyOverride']);

        // VALIDATION
        if (empty($data['callId'])) {
            return response('missing required params', 401);
        }
        if ( ! Auth::user()) {
            return response('missing required scheduler user', 401);
        }

        // find call
        $call = Call::whereId($data['callId'])->with('inboundUser')->first();
        if ( ! $call) {
            return response('could not locate call '.$data['callId'], 401);
        }

        $col   = $data['columnName'];
        $value = $data['value'];

        //software-only check - CPM-660 - practice admin cannot change clh nurse to in-house nurse
        if ('outbound_cpm_id' == $col) {
            $canUpdateCareCoach = $this->canAssignCareCoachToActivity($call, $value);
            if ( ! $canUpdateCareCoach) {
                return response(
                    'cannot update change care-coach',
                    421
                );
            }
        }

        if (in_array($col, $columnsToCheckForOverride)
            && ! $isFamilyOverride
            && $call->inboundUser
            && $call->inboundUser->patientInfo) {
            $mustConfirm = false;
            switch ($col) {
                case 'scheduled_date':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime(
                        $call->inboundUser->patientInfo,
                        $value,
                        $call->window_start,
                        $call->window_end
                    );
                    break;
                case 'window_start':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime(
                        $call->inboundUser->patientInfo,
                        $call->scheduled_date,
                        $value,
                        $call->window_end
                    );
                    break;
                case 'window_end':
                    $mustConfirm = $this->hasAlreadyFamilyCallAtDifferentTime(
                        $call->inboundUser->patientInfo,
                        $call->scheduled_date,
                        $call->window_start,
                        $value
                    );
                    break;
            }

            if ($mustConfirm) {
                return response(
                    'patient belongs to family and the family has a call at different time',
                    418
                );
            }
        }

        if ($isFamilyOverride) {
            $call->is_manual = true;
        }

        // for null outbound_cpm_id
        if ('outbound_cpm_id' == $col && (empty($value) || 'unassigned' == strtolower($value))) {
            $call->scheduler = Auth::user()->id;
            $call->$col      = null;
        } elseif ('attempt_note' == $col && (empty($value) || 'add text' == strtolower($value))) {
            $call->attempt_note = '';
        } elseif ('general_comment' == $col) {
            if ((empty($value) || 'add text' == strtolower($value))) {
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
            if ('outbound_cpm_id' != $col) {
                $call->scheduler = Auth::id();
            }
        }

        $call->save();

        if ('outbound_cpm_id' === $col) {
            $this->processNursePatientRelation($call->inboundUser, [
                'outbound_cpm_id' => $data['value'],
            ]);
        }

        return response(
            'successfully updated call '.$data['columnName'].'='.$data['value'].' - CallId='.$data['callId'],
            201
        );
    }

    private function alreadyHasScheduledCall(User $patient): bool
    {
        return $patient->inboundCalls()
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', 'call');
            })
            ->where('status', '=', 'scheduled')
            ->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'))
            ->exists();
    }

    /**
     * Software-Only role cannot change clh nurse to in-house nurse
     * CPM-660.
     *
     * @param $newCareCoachUserId
     *
     * @return bool
     */
    private function canAssignCareCoachToActivity(Call $call, $newCareCoachUserId)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return true;
        }

        //get practice of patient
        $patientPrimaryPractice = $call->inboundUser->primaryPractice->id;

        //check if user has software-only role for practice of patient
        if ( ! $user->hasRoleForSite('software-only', $patientPrimaryPractice)) {
            return false;
        }

        //check role of current care coach
        $currentIsClhCareCoach = $call->outboundUser->hasRoleForSite('care-center', $patientPrimaryPractice);
        $newIsClhCareCoach     = User::find($newCareCoachUserId)->hasRoleForSite(
            'care-center',
            $patientPrimaryPractice
        );

        if ($currentIsClhCareCoach && ! $newIsClhCareCoach) {
            return false;
        }

        //current care-coach is clh and new is also clh care-coach
        //current care-coach is not clh and new is not clh care-coach
        //current care-coach is not clh and new is clh care-coach
        return true;
    }

    /**
     * @param $input
     *
     * @return array|static
     */
    private function createCall(array $input)
    {
        $validation = \Validator::make($input, [
            'type'            => 'required',
            'sub_type'        => '',
            'inbound_cpm_id'  => 'required',
            'outbound_cpm_id' => '',
            'scheduled_date'  => ['required', 'after_or_equal:today', new DateBeforeUsingCarbon()],
            'window_start'    => 'required|date_format:H:i',
            'window_end'      => 'required|date_format:H:i',
            'attempt_note'    => '',
            'is_manual'       => 'required|boolean',
            'family_override' => '',
            'asap'            => '',
            'is_reschedule'   => 'sometimes|boolean',
        ]);

        if ($validation->fails()) {
            return [
                'errors' => $validation->errors()->getMessages(),
                'code'   => 422,
            ];
        }

        if ('task' === $input['type'] && empty($input['sub_type'])) {
            return [
                'errors' => ['invalid form'],
                'code'   => 407,
            ];
        }

        $isCallBack       = ! empty($input['sub_type']) && SchedulerService::CALL_BACK_TYPE === $input['sub_type'];
        $isFamilyOverride = ! empty($input['family_override']);

        if ($isCallBack || ! $isFamilyOverride) {
            $patient = User::without(['roles', 'perms'])->with('patientInfo')->find($input['inbound_cpm_id']);
        } else {
            $patient = User::without(['roles', 'perms'])->find($input['inbound_cpm_id']);
        }

        if ( ! $patient) {
            return [
                'errors' => ['could not find patient'],
                'code'   => 406,
            ];
        }

        if ('call' === $input['type'] && $this->alreadyHasScheduledCall($patient)) {
            return [
                'errors' => ['patient already has a scheduled call'],
                'code'   => 406,
            ];
        }

        if ( ! $isFamilyOverride
            && $this->hasAlreadyFamilyCallAtDifferentTime(
                $patient->patientInfo,
                $input['scheduled_date'],
                $input['window_start'],
                $input['window_end']
            )) {
            return [
                'errors' => ['patient belongs to family and the family has a call at different time'],
                'code'   => 418,
            ];
        }

        if ($isCallBack) {
            $input['outbound_cpm_id'] = $this->scheduler->handleSchedulingCallBack($patient, $input['outbound_cpm_id']);
            $patient->patientInfo->save();
        }

        $call = $this->storeNewCall($patient, $input);

        if ('call' === $input['type']) {
            $this->storeNewCallForFamilyMembers($patient, $input);
        }

        return CallResource::make($call);
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

    private function processNursePatientRelation(User $patient, $input)
    {
        if ( ! auth()->check() || ! $this->canChangeNursePatientRelation(auth()->user())) {
            return;
        }

        $isReschedule = $input['is_reschedule'] ?? false;

        if ($isReschedule) {
            return;
        }
        if (is_numeric($input['outbound_cpm_id'])) {
            app(NurseFinderEloquentRepository::class)->assign($patient->id, (int) $input['outbound_cpm_id']);
        }
    }

    /**
     * @param $input
     *
     * @return Call
     */
    private function storeNewCall(User $user, $input)
    {
        $scheduledDate = $input['scheduled_date'];
        $windowStart   = $input['window_start'];
        $windowEnd     = $input['window_end'];

        if ( ! ($scheduledDate instanceof Carbon)) {
            $scheduledDate = Carbon::parse($scheduledDate);
        }

        if ( ! ($windowStart instanceof Carbon)) {
            $windowStart = Carbon::parse($windowStart);
        }

        if ( ! ($windowEnd instanceof Carbon)) {
            $windowEnd = Carbon::parse($windowEnd);
        }

        $isFamilyOverride = ! empty($input['family_override']);

        $call           = new Call();
        $call->type     = $input['type'];
        $call->sub_type = isset($input['sub_type'])
            ? $input['sub_type']
            : null;
        $call->inbound_cpm_id = $user->id;
        $call->asap           = boolval($input['asap'] ?? false);
        //make sure we are sending the dates correctly formatted
        $call->scheduled_date  = $scheduledDate->format('Y-m-d');
        $call->window_start    = $windowStart->format('H:i');
        $call->window_end      = $windowEnd->format('H:i');
        $call->attempt_note    = $input['attempt_note'];
        $call->note_id         = null;
        $call->is_cpm_outbound = 1;
        $call->service         = 'phone';
        $call->status          = Call::SCHEDULED;
        $call->scheduler       = auth()->user()->id;
        $call->is_manual       = boolval($input['is_manual']) || $isFamilyOverride;

        if (empty($input['outbound_cpm_id'])) {
            $call->outbound_cpm_id = null;
        } else {
            $call->outbound_cpm_id = $input['outbound_cpm_id'];
        }

        $call->save();

        $this->processNursePatientRelation($user, $input);

        return $call;
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
}
