<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Twilio;

use App\Http\Controllers\Controller;
use App\RedisEvents\TwilioDebuggerEvent;
use App\Services\TwilioClientable;
use App\TwilioCall;
use App\TwilioConferenceCallParticipant;
use App\TwilioRawLog;
use App\TwilioRecording;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\TwilioDebuggerLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SimpleXMLElement;
use Twilio\Exceptions\TwimlException;
use Twilio\Twiml;

class TwilioController extends Controller
{
    const CLIENT_ANONYMOUS = 'client:Anonymous';
    const CONFERENCE_END = 'conference-end';
    const CONFERENCE_PARTICIPANT_JOIN = 'participant-join';
    const CONFERENCE_PARTICIPANT_LEAVE = 'participant-leave';

    const CONFERENCE_START = 'conference-start';

    const DIAL_TIMEOUT_SECONDS = 90;
    private $client;

    private $service;

    public function __construct(TwilioClientable $twilioClientService)
    {
        $this->service = $twilioClientService;
        $this->client  = $twilioClientService->getClient();
    }

    /**
     * This function is called from Twilio (status callback)
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request from Twilio and creates or updates any existing calls (using call sid).
     *
     * @param Request $request
     */
    public function callStatusCallback(Request $request)
    {
        $this->logRawToDb($request);
        $this->logParentCallToDb($request);
    }

    /**
     * This function is called from Twilio (conference status callback)
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request and creates or updates a conference.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response XML Empty Response
     * @throws TwimlException
     *
     */
    public function conferenceStatusCallback(Request $request)
    {
        $this->logRawToDb($request, 'conference-status-callback');
        $this->logConferenceToDb($request);

        return $this->responseWithXmlType(response(new Twiml()));
    }

    /**
     * This function is called from Twilio (Dial Action URL - see placeCall above)
     * When the call ends, this handler is called (different from callStatusCallback below)
     * This handler decides what happens next:
     * We simply log the status and duration and hang up.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dialActionCallback(Request $request)
    {
        $this->logRawToDb($request, 'dial-action-callback');
        $this->logDialActionToDb($request);

        try {
            $response = new Twiml();

            $isFromChildLeg = $request->has('ParentCallSid');
            if ($isFromChildLeg) {
                $childCallStatus = $request->input('DialCallStatus');

                //this is a completed call (i.e. the user hung up the phone
                if ($childCallStatus && 'completed' === $childCallStatus) {
                    $response->hangup();
                } else {
                    $call = TwilioCall::where('call_sid', '=', $request->input('ParentCallSid'))
                                      ->first();
                    if ( ! $call) {
                        $response->hangup();
                    } else {
                        $isCallUpdateToConference = null == $childCallStatus;
                        $parentCallStatus         = $request->input('CallStatus');

                        if ($call->in_conference && $isCallUpdateToConference && 'in-progress' === $parentCallStatus) {
                            $recordCalls = config('services.twilio.allow-recording');
                            if ($recordCalls) {
                                $inboundUser = User::whereHas('primaryPractice')->find($call->inbound_user_id);
                                if ($inboundUser) {
                                    $recordCalls = $inboundUser->primaryPractice->cpmSettings()->twilio_recordings_enabled;
                                }
                            }

                            //the patient
                            //conference still runs even on exit
                            //means that the nurse has to explicitly hang up to end the call
                            $conferenceName = $call->inbound_user_id . '_' . $call->outbound_user_id;
                            $dial           = $response->dial();
                            $dial->conference($conferenceName, [
                                'endConferenceOnExit'           => false,
                                'statusCallbackEvent'           => 'start end join leave',
                                'statusCallback'                => route('twilio.call.conference.status'),
                                'statusCallbackMethod'          => 'POST',
                                'record'                        => $recordCalls
                                    ? 'record-from-start'
                                    : 'do-not-record',
                                'recordingStatusCallback'       => route('twilio.call.recording.status'),
                                'recordingStatusCallbackMethod' => 'POST',
                                'recordingStatusCallbackEvent'  => 'completed',
                            ]);
                        } else {
                            $response->hangup();
                        }
                    }
                }
            } else {
                $call = TwilioCall::where('call_sid', '=', $request->input('CallSid'))->first();

                if ( ! $call) {
                    $response->hangup();
                } else {
                    if ($call->in_conference) {
                        $recordCalls = config('services.twilio.allow-recording');
                        if ($recordCalls) {
                            $inboundUser = User::whereHas('primaryPractice')->find($call->inbound_user_id);
                            if ($inboundUser) {
                                $recordCalls = $inboundUser->primaryPractice->cpmSettings()->twilio_recordings_enabled;
                            }
                        }

                        //the nurse
                        //conference starts immediately
                        //conference ends on exit
                        $conferenceName = $call->inbound_user_id . '_' . $call->outbound_user_id;
                        $dial           = $response->dial();
                        $dial->conference($conferenceName, [
                            'startConferenceOnEnter'        => true,
                            'endConferenceOnExit'           => true,
                            'statusCallbackEvent'           => 'start end join leave',
                            'statusCallback'                => route('twilio.call.conference.status'),
                            'statusCallbackMethod'          => 'POST',
                            'record'                        => $recordCalls
                                ? 'record-from-start'
                                : 'do-not-record',
                            'recordingStatusCallback'       => route('twilio.call.recording.status'),
                            'recordingStatusCallbackMethod' => 'POST',
                            'recordingStatusCallbackEvent'  => 'completed',
                        ]);
                    } else {
                        $response->hangup();
                    }
                }
            }

            return $this->responseWithXmlType(response($response));
        } catch (TwimlException $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
            return $this->responseWithXmlData(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * This function is called from Twilio (status callback)
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request from Twilio and creates or updates any existing calls (using call sid).
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response XML Empty Response
     * @throws TwimlException
     *
     */
    public function dialNumberStatusCallback(Request $request)
    {
        $this->logRawToDb($request, 'dial-number-status-callback');
        $this->logDialToDb($request);

        return $this->responseWithXmlType(response(new Twiml()));
    }

    /**
     * End a call using an sid. Usually used to end a call in a conference.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function endCall(Request $request)
    {
        $input      = $request->all();
        $validation = Validator::make($input, [
            'CallSid'        => 'required',
            'InboundUserId'  => 'required',
            'OutboundUserId' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        $this->client->calls($input['CallSid'])
                     ->update(['status' => 'completed']);

        return response()->json([]);
    }

    /**
     * Get conference info using inbound user id and outbound user id.
     * These two fields make the conference friendly name.
     * Returns the conference sid and participant's sid(s).
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConferenceInfo(Request $request)
    {
        $input = $request->all();

        $validation = Validator::make($input, [
            'inbound_user_id'  => 'required',
            'outbound_user_id' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        $confs = $this->client->conferences->read([
            'FriendlyName' => $input['inbound_user_id'] . '_' . $input['outbound_user_id'],
        ]);

        if (empty($confs)) {
            return response()->json(['errors' => ['not found']]);
        }

        $conference = $confs[0];

        $participantsInfo = collect($this->client->conferences($conference->sid)->participants->read())
            ->map(function ($participant) {
                $call = $this->client->calls($participant->callSid)->fetch();

                return [
                    'from'     => $call->from,
                    'to'       => $call->to,
                    'call_sid' => $participant->callSid,
                    'status'   => $call->status,
                ];
            });

        return response()->json(
            [
                'sid'          => $conference->sid,
                'status'       => $conference->status,
                'participants' => $participantsInfo,
            ]
        );
    }

    /**
     * Join an active conference using the conference sid.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinConference(Request $request)
    {
        $input = $request->all();

        $isProduction = config('app.env') === 'production';

        if (empty($input['From']) || TwilioController::CLIENT_ANONYMOUS === $input['From']) {
            $input['From'] = config('services.twilio')['from'];
        }

        $input['From'] = formatPhoneNumberE164($input['From']);
        //$input['To'] = formatPhoneNumberE164($input['To']);

        //why do I have to do this?
        if ( ! empty($input['IsUnlistedNumber'])) {
            $input['IsUnlistedNumber'] = boolValue($input['IsUnlistedNumber']);
        }

        if ( ! empty($input['IsCallToPatient'])) {
            $input['IsCallToPatient'] = boolValue($input['IsCallToPatient']);
        }

        $validation = Validator::make($input, [
            //could be the practice outgoing phone number (in case of enrollment)
            'From'             => 'required|phone:AUTO,US',
            'To'               => [
                'required',
                $isProduction
                    ? Rule::phone()->detect()->country('US')
                    : '',
            ],
            'InboundUserId'    => 'required|numeric',
            'OutboundUserId'   => 'required|numeric',
            'IsUnlistedNumber' => 'nullable|boolean',
            'IsCallToPatient'  => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        if ($input['IsUnlistedNumber']) {
            $this->sendUnlistedNumberToSlack($input);
        }

        $confs = $this->client->conferences->read([
            'FriendlyName' => $input['InboundUserId'] . '_' . $input['OutboundUserId'],
            'Status'       => 'in-progress',
        ]);

        $conference = $confs[0];

        $participant = $this->client->conferences($conference->sid)
            ->participants
            ->create($input['From'], $input['To'], [
                'endConferenceOnExit' => false,
                'timeout'             => TwilioController::DIAL_TIMEOUT_SECONDS,
            ]);

        return response()->json(['call_sid' => $participant->callSid]);
    }

    /**
     * Initiated from client-side. This handler will:
     * - Trigger an update to the current call session
     * - This trigger will call dialActionCallback, which will return with a Twiml Conference
     * - The dialActionCallback will be called again, this time for the parent call session,
     *   which will also return with a Twiml Conference.
     *
     * So, the parent call (inbound) and the child call leg (outbound) will move to a conference
     * without hanging up!
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsCreateConference(Request $request)
    {
        $input      = $request->all();
        $validation = Validator::make($input, [
            'inbound_user_id'  => 'required',
            'outbound_user_id' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        $dbCall = TwilioCall::where('inbound_user_id', '=', $input['inbound_user_id'])
                            ->where('outbound_user_id', '=', $input['outbound_user_id'])
                            ->where(function ($q) {
                                $q->where('call_status', '=', 'ringing')
                                  ->orWhere('call_status', '=', 'in-progress');
                            })
                            ->orderBy('updated_at', 'desc')
                            ->first();

        if ( ! $dbCall) {
            return response()->json(['errors' => ['could not find active call with user ids supplied']]);
        }

        try {
            $dbCall->in_conference = true;
            $dbCall->save();

            $calls       = $this->client->calls->read(['parentCallSid' => $dbCall->call_sid]);
            $dialCallSid = $calls[0]->sid;

            $this->client->calls($dialCallSid)
                         ->update(
                             [
                                 'method' => 'POST',
                                 'url'    => route('twilio.call.dial.action'),
                             ]
                         );

            return response()->json([]);
        } catch (\Exception $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
            return response()->json(['errors' => [$e->getMessage()]]);
        }
    }

    public function obtainToken()
    {
        return response()->json(['token' => $this->service->generateCapabilityToken()]);
    }

    /**
     * This handler is the Twilio Voice URL. This is set in Twilio Console, as a Twiml App.
     * The custom parameters are:
     * To,
     * From,
     * InboundUserId - the target user we are calling (a patient or a practice)
     * OutboundUserId - the user making the call (i.e nurse)
     * IsUnlistedNumber - has value if the number we are calling is manually inserted from the client side
     * IsCallToPatient - true if calling a patient, false if calling another party (eg patient's practice).
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Twilio\Exceptions\TwimlException
     *
     */
    public function placeCall(Request $request)
    {
        $this->logRawToDb($request, 'init');

        $input = $request->all();

        $isProduction = config('app.env') === 'production';

        if (empty($input['From']) || TwilioController::CLIENT_ANONYMOUS === $input['From']) {
            $input['From'] = config('services.twilio')['from'];
        }

        $input['From'] = formatPhoneNumberE164($input['From']);
        //$input['To'] = formatPhoneNumberE164($input['To']);

        //why do I have to do this?
        if ( ! empty($input['IsUnlistedNumber'])) {
            $input['IsUnlistedNumber'] = boolValue($input['IsUnlistedNumber']);
        }

        if ( ! empty($input['IsCallToPatient'])) {
            $input['IsCallToPatient'] = boolValue($input['IsCallToPatient']);
        }

        $validation = Validator::make($input, [
            //could be the practice outgoing phone number (in case of enrollment)
            'From'              => 'required|phone:AUTO,US',
            'To'                => [
                'required',
                $isProduction
                    ? Rule::phone()->detect()->country('US')
                    : '',
            ],
            //could be null in case of Enrollee without a User model
            'InboundUserId'     => '',
            'InboundEnrolleeId' => '',
            'OutboundUserId'    => 'required|numeric',
            'IsUnlistedNumber'  => 'nullable|boolean',
            'IsCallToPatient'   => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return $this->responseWithXmlData($validation->errors()->all(), 400);
        }

        if ($input['IsUnlistedNumber']) {
            $this->sendUnlistedNumberToSlack($input);
        }

        $this->logParentCallToDb($request);

        $response = new Twiml();
        $dial     = $response->dial('', [
            //action url will tell us the duration of this call and the status of it when it ends
            'action'                        => route('twilio.call.dial.action'),
            'callerId'                      => $input['From'],
            'record'                        => config('services.twilio.allow-recording')
                ? 'record-from-answer'
                : 'do-not-record',
            'recordingStatusCallback'       => route('twilio.call.recording.status'),
            'recordingStatusCallbackMethod' => 'POST',
            'recordingStatusCallbackEvent'  => 'completed',
            'timeout'                       => TwilioController::DIAL_TIMEOUT_SECONDS,
        ]);
        $dial->number($input['To'], [
            'statusCallback'      => route('twilio.call.number.status'),
            'statusCallbackEvent' => 'completed',
        ]);

        return $this->responseWithXmlType(response($response));
    }

    /**
     * This function is called from Twilio (recording status callback)
     * It is called when recording an Outbound-Dial or Conference
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request and creates or updates a recording in our DB.
     *
     * @param Request $request
     */
    public function recordingStatusCallback(Request $request)
    {
        $this->logRawToDb($request, 'recording-status-callback');
        $this->logRecordingToDb($request);
    }

    /**
     * Called from Twilio Debugger
     *
     * @param Request $request
     */
    public function debuggerWebhook(Request $request)
    {
        $ts = Carbon::parse($request->input('Timestamp'), 'UTC');
        $ts = $ts->setTimezone(config('app.timezone'));

        $log = TwilioDebuggerLog::create([
            'sid'                => $request->input('Sid'),
            'account_sid'        => $request->input('AccountSid'),
            'parent_account_sid' => $request->input('ParentAccountSid'),
            'event_timestamp'    => $ts,
            'level'              => $request->input('Level'),
            'payload'            => $request->input('Payload'),
        ]);

        (new TwilioDebuggerEvent($log->id))->publish();

        return $this->responseWithXmlType(response(''));
    }

    /**
     * Update TwilioCall model with conference sid, status, duration, recording url
     * Update TwilioConferenceCallParticipants model with conference sid, and participants info (sid, number, status,
     * duration).
     *
     * This callback handles 'start' and 'end' events.
     * If we decide to handle 'join' and 'leave' events, the CallSid(s) will represent the sids for the individual call
     * legs.
     *
     * NOTE: Conference Duration and Status are sent in recording status callback.
     *
     * @param Request $request
     */
    private function logConferenceToDb(Request $request)
    {
        try {
            $input = $request->all();

            $callbackEvent = $input['StatusCallbackEvent'];
            $conferenceSid = $input['ConferenceSid'];
            $fields        = [
                'conference_sid'    => $conferenceSid,
                'conference_status' => 'conference-end' === $callbackEvent
                    ? 'completed'
                    : 'in-progress',
            ];

            if ( ! empty($input['FriendlyName'])) {
                $fields['conference_friendly_name'] = $input['FriendlyName'];
            }

            if (TwilioController::CONFERENCE_END === $callbackEvent) {
                //at this point, the conference_sid should be in the twilio_calls table
                TwilioCall::updateOrCreate(
                    ['conference_sid' => $conferenceSid],
                    $fields
                );
            }

            if (TwilioController::CONFERENCE_PARTICIPANT_JOIN === $callbackEvent || TwilioController::CONFERENCE_PARTICIPANT_LEAVE === $callbackEvent) {
                $call = $this->client->calls($input['CallSid'])->fetch();

                //callbacks arrive asynchronously
                //there is high chance that the conference-end will be received and then the participant-leave
                $shouldUpdateTwilioCallsTable = TwilioCall::where('call_sid', '=', $call->sid)
                                                          ->whereNull('conference_status')
                                                          ->exists();
                if ($shouldUpdateTwilioCallsTable) {
                    TwilioCall::updateOrCreate(
                        ['call_sid' => $call->sid],
                        $fields
                    );
                }

                $participantFields = [
                    'account_sid'    => $input['AccountSid'],
                    'call_sid'       => $input['CallSid'],
                    'conference_sid' => $conferenceSid,
                    'duration'       => $call->duration ?? 0,
                    'status'         => $call->status,
                ];

                $participantNumber = $call->to;
                if ( ! empty($participantNumber)) {
                    $participantFields['participant_number'] = $participantNumber;
                }

                $participant = TwilioConferenceCallParticipant::updateOrCreate(
                    [
                        'call_sid'       => $input['CallSid'],
                        'conference_sid' => $conferenceSid,
                    ],
                    $participantFields
                );

                //$call->from is usually client:Anonymous. the correct value will be set in dial-number-status-callback
                //we have to check the value we have in DB before setting client:Anonymous though, because the correct
                //number might have already been set in db. consider this scenario:
                // 1. participant-join (client:Anonymous).
                // 2. dial-number-status-callback (correct number).
                // 3. participant-leave (client:Anonymous) -> should not set this value
                if (empty($participant->participant_number)) {
                    $participant->participant_number = $call->from;
                    $participant->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio conference log: ' . $e->getMessage());
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    /**
     * Log sequence number of a dial call
     * The rest we can get from dialNumberStatusCallback.
     *
     * The $request will have ParentCallSid when the action callback is made for the child leg.
     * It will not have ParentCallSid when its made for the root leg.
     *
     * @param Request $request
     */
    private function logDialActionToDb(Request $request)
    {
        $input = $request->all();

        //CallSid in dial action url is the sid of the main call leg (the parent)
        $callSid = ! empty($input['ParentCallSid'])
            ? $input['ParentCallSid']
            : $input['CallSid'];

        $fields = [
            'call_sid' => $callSid,
        ];

        if ( ! empty($input['SequenceNumber'])) {
            $fields['sequence_number'] = $input['SequenceNumber'];
        }

        TwilioCall::updateOrCreate(
            ['call_sid' => $callSid],
            $fields
        );
    }

    private function logDialToDb(Request $request)
    {
        try {
            $input = $request->all();

            $callSid = $input['ParentCallSid'];

            $fields = [
                'call_sid' => $callSid,
            ];

            if ( ! empty($input['ApplicationSid'])) {
                $fields['application_sid'] = $input['ApplicationSid'];
            }

            if ( ! empty($input['AccountSid'])) {
                $fields['account_sid'] = $input['AccountSid'];
            }

            if ( ! empty($input['Direction'])) {
                $fields['direction'] = $input['Direction'];
            }

            if ( ! empty($input['From'])) {
                $fields['from'] = $input['From'];
            }

            if ( ! empty($input['To'])) {
                $fields['to'] = $input['To'];
            }

            if ( ! empty($input['SequenceNumber'])) {
                $fields['sequence_number'] = $input['SequenceNumber'];
            }

            // For the next properties, we append dial_ because we want to keep information from parent call as well

            if ( ! empty($input['CallSid'])) {
                $fields['dial_call_sid'] = $input['CallSid'];
            }

            if ( ! empty($input['CallDuration'])) {
                $fields['dial_conference_duration'] = intValue($input['CallDuration']);
            }

            if ( ! empty($input['CallStatus'])) {
                $fields['dial_call_status'] = $input['CallStatus'];
            }

            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                $fields
            );

            //if this call is a participant in a conference, we might need to set the correct participant_number
            //this happens because in the conference callbacks we do not have the actual number but a 'client:Anonymous'
            if ( ! empty($fields['from'])) {
                $conferenceParticipant = TwilioConferenceCallParticipant::where('call_sid', '=', $callSid)
                                                                        ->where(
                                                                            'participant_number',
                                                                            '=',
                                                                            TwilioController::CLIENT_ANONYMOUS
                                                                        )
                                                                        ->first();

                if ($conferenceParticipant) {
                    $conferenceParticipant->participant_number = $fields['from'];
                    $conferenceParticipant->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio log: ' . $e->getMessage());
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    private function logParentCallToDb(Request $request)
    {
        try {
            $input = $request->all();

            $callSid = $input['CallSid'];

            $fields = [
                'call_sid' => $callSid,
            ];

            if ( ! empty($input['ApplicationSid'])) {
                $fields['application_sid'] = $input['ApplicationSid'];
            }

            if ( ! empty($input['AccountSid'])) {
                $fields['account_sid'] = $input['AccountSid'];
            }

            if ( ! empty($input['CallStatus'])) {
                $fields['call_status'] = $input['CallStatus'];
            }

            //only present in 'completed' status event
            if ( ! empty($input['CallDuration'])) {
                $fields['call_duration'] = intValue($input['CallDuration']);
            }

            if ( ! empty($input['InboundUserId'])) {
                $fields['inbound_user_id'] = intValue($input['InboundUserId'], null);
            }

            if ( ! empty($input['InboundEnrolleeId'])) {
                $fields['inbound_enrollee_id'] = intValue($input['InboundEnrolleeId'], null);
            }

            if ( ! empty($input['OutboundUserId'])) {
                $fields['outbound_user_id'] = intValue($input['OutboundUserId']);
            }

            if ( ! empty($input['IsUnlistedNumber'])) {
                $fields['is_unlisted_number'] = boolValue($input['IsUnlistedNumber']);
            }

            if ( ! empty($input['SequenceNumber'])) {
                $fields['sequence_number'] = $input['SequenceNumber'];
            }

            if ( ! empty($input['Source'])) {
                $fields['source'] = $input['Source'];
            }

            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                $fields
            );
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio log: ' . $e->getMessage());
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    private function logRawToDb(Request $request, $type = null)
    {
        try {
            TwilioRawLog::create([
                'application_sid' => $request->get('ApplicationSid'),
                'account_sid'     => $request->get('AccountSid'),
                'call_sid'        => $request->get('CallSid'),
                'call_status'     => $request->get('CallStatus'),
                'log'             => json_encode($request->all()),
                'type'            => null == $type
                    ? $request->get('CallbackSource', null)
                    : $type,
            ]);
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio raw log: ' . $e->getMessage());
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    /**
     * Update TwilioCall model with call_recording_sid or conference_recording_sid
     * Update TwilioCallRecordings with account sid, call or conference sid, recording sid, url, status, duration.
     *
     * @param Request $request
     */
    private function logRecordingToDb(Request $request)
    {
        try {
            $input = $request->all();

            $recordingSid = $input['RecordingSid'];
            $accountSid   = $input['AccountSid'];
            $status       = $input['RecordingStatus'];
            $source       = $input['RecordingSource'];

            $recordingFields = [
                'recording_sid' => $recordingSid,
                'account_sid'   => $accountSid,
                'status'        => $status,
                'source'        => $source,
            ];

            if ( ! empty($input['ConferenceSid'])) {
                $recordingFields['conference_sid'] = $input['ConferenceSid'];
            }

            if (empty($input['CallSid'])) {
                if (empty($input['ConferenceSid'])) {
                    //should never happen
                    throw new \Exception('recording has no CallSid nor ConferenceSid');
                }

                $conferenceSid = $input['ConferenceSid'];
                $call          = TwilioCall::select('call_sid')->where(
                    'conference_sid',
                    '=',
                    $conferenceSid
                )->first();

                if ( ! $call) {
                    throw new \Exception("call_sid not found when querying with conference_sid[$conferenceSid]");
                }

                $callSid = $call->call_sid;
            } else {
                $callSid                     = $input['CallSid'];
                $recordingFields['call_sid'] = $callSid;
            }

            if ( ! empty($input['RecordingUrl'])) {
                $recordingFields['url'] = $input['RecordingUrl'];
            }

            if ( ! empty($input['RecordingDuration'])) {
                $recordingFields['duration'] = $input['RecordingDuration'];
            }

            if (empty($recordingFields['conference_sid'])) {
                TwilioRecording::updateOrCreate(
                    ['call_sid' => $callSid],
                    $recordingFields
                );
                TwilioCall::updateOrCreate(
                    ['call_sid' => $callSid],
                    ['dial_recording_sid' => $recordingSid]
                );
            } else {
                TwilioRecording::updateOrCreate(
                    ['call_sid' => $callSid, 'conference_sid' => $recordingFields['conference_sid']],
                    $recordingFields
                );
                TwilioCall::updateOrCreate(
                    ['call_sid' => $callSid],
                    ['conference_recording_sid' => $recordingSid]
                );
            }
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio recording log: ' . $e->getMessage());
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    private function responseWithXmlData(
        array $vars,
        $status = 200,
        array $header = [],
        $rootElement = 'response',
        $xml = null
    ) {
        if (is_null($xml)) {
            $xml = new SimpleXMLElement('<' . $rootElement . '/>');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                $this->responseWithXmlData($value, $status, $header, $rootElement, $xml->addChild($key));
            } else {
                if (preg_match('/^@.+/', $key)) {
                    $attributeName = preg_replace('/^@/', '', $key);
                    $xml->addAttribute($attributeName, $value);
                } else {
                    $xml->addChild($key, $value);
                }
            }
        }

        if (empty($header)) {
            $header['Content-Type'] = 'application/xml';
        }

        return Response::make($xml->asXML(), $status, $header);
    }

    private function responseWithXmlType(\Illuminate\Http\Response $response)
    {
        return $response->header('Content-Type', 'application/xml');
    }

    private function sendUnlistedNumberToSlack($input)
    {
        return;
        //need to install this package https://github.com/jeremykenedy/slack-laravel
        /*
        $userId         = $input['OutboundUserId'];
        $unlistedNumber = $input['To'];
        sendSlackMessage(
            '#twilio-calls',
            "User [$userId] is trying to call a non-predefined phone number [$unlistedNumber]."
        );
        */
    }
}
