<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Twilio;

use App\Contracts\Services\TwilioClientable;
use App\Enrollee;
use App\Http\Controllers\Controller;
use App\TwilioCall;
use App\TwilioRawLog;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Twilio\Exceptions\TwimlException;
use Twilio\Twiml;

class TwilioController extends Controller
{
    const CLIENT_ANONYMOUS = 'client:Anonymous';
    private $client;
    private $token;

    public function __construct(TwilioClientable $twilioClientService)
    {
        $this->client = $twilioClientService->getClient();
        $this->token  = $twilioClientService->generateCapabilityToken();
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
                            $conferenceName = $call->inbound_user_id.'_'.$call->outbound_user_id;
                            $dial           = $response->dial();
                            $dial->conference($conferenceName, [
                                'endConferenceOnExit' => false,
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
                        $conferenceName = $call->inbound_user_id.'_'.$call->outbound_user_id;
                        $dial           = $response->dial();
                        $dial->conference($conferenceName, [
                            'endConferenceOnExit' => true,
                        ]);
                    } else {
                        $response->hangup();
                    }
                }
            }

            return $this->responseWithXmlType(response($response));
        } catch (TwimlException $e) {
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
     * @throws TwimlException
     *
     * @return Twiml XML Empty Response
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
        $validation = \Validator::make($input, [
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

        $validation = \Validator::make($input, [
            'inbound_user_id'  => 'required',
            'outbound_user_id' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        $confs = $this->client->conferences->read([
            'FriendlyName' => $input['inbound_user_id'].'_'.$input['outbound_user_id'],
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

        $isProduction = isProductionEnv();

        //use default From number if we are not on production
        if ( ! $isProduction || empty($input['From']) || TwilioController::CLIENT_ANONYMOUS === $input['From']) {
            $input['From'] = config('services.twilio')['from'];
        }

        $input['From'] = formatPhoneNumberE164($input['From']);
        //$input['To'] = formatPhoneNumberE164($input['To']);

        //why do I have to do this?
        if ( ! empty($input['IsUnlistedNumber'])) {
            $input['IsUnlistedNumber'] = '1' === $input['IsUnlistedNumber']
                ? true
                : false;
        }

        if ( ! empty($input['IsCallToPatient'])) {
            $input['IsCallToPatient'] = '1' === $input['IsCallToPatient']
                ? true
                : false;
        }

        $validation = \Validator::make($input, [
            //could be the practice outgoing phone number (in case of enrollment)
            'From' => 'required|phone:AUTO,US',
            'To'   => [
                'required',
                $isProduction
                    ? Rule::phone()->detect()->country('US')
                    : '',
            ],
            'InboundUserId'    => 'required',
            'OutboundUserId'   => 'required',
            'IsUnlistedNumber' => 'nullable|boolean',
            'IsCallToPatient'  => 'nullable|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors()->all());
        }

        $confs = $this->client->conferences->read([
            'FriendlyName' => $input['InboundUserId'].'_'.$input['OutboundUserId'],
            'Status'       => 'in-progress',
        ]);

        $conference = $confs[0];

        $participant = $this->client->conferences($conference->sid)
            ->participants
            ->create($input['From'], $input['To'], [
                'endConferenceOnExit' => false,
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
        $validation = \Validator::make($input, [
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
            return response()->json(['errors' => [$e->getMessage()]]);
        }
    }

    public function obtainToken()
    {
        return response()->json(['token' => $this->token]);
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
     * @throws \Twilio\Exceptions\TwimlException
     *
     * @return mixed
     */
    public function placeCall(Request $request)
    {
        $this->logRawToDb($request, 'init');

        $input = $request->all();

        $isProduction = isProductionEnv();

        //use default From number if we are not on production
        if ( ! $isProduction || empty($input['From']) || TwilioController::CLIENT_ANONYMOUS === $input['From']) {
            $input['From'] = config('services.twilio')['from'];
        }

        $input['From'] = formatPhoneNumberE164($input['From']);
        //$input['To'] = formatPhoneNumberE164($input['To']);

        //why do I have to do this?
        if ( ! empty($input['IsUnlistedNumber'])) {
            $input['IsUnlistedNumber'] = '1' === $input['IsUnlistedNumber']
                ? true
                : false;
        }

        if ( ! empty($input['IsCallToPatient'])) {
            $input['IsCallToPatient'] = '1' === $input['IsCallToPatient']
                ? true
                : false;
        }

        $validation = \Validator::make($input, [
            //could be the practice outgoing phone number (in case of enrollment)
            'From' => 'required|phone:AUTO,US',
            'To'   => [
                'required',
                $isProduction
                    ? Rule::phone()->detect()->country('US')
                    : '',
            ],
            'InboundUserId'    => 'required',
            'OutboundUserId'   => 'required',
            'IsUnlistedNumber' => 'nullable|boolean',
            'IsCallToPatient'  => 'nullable|boolean',
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
            'action'   => route('twilio.call.dial.action'),
            'callerId' => $input['From'],
        ]);
        $dial->number($input['To'], [
            'statusCallback'      => route('twilio.call.number.status'),
            'statusCallbackEvent' => 'completed',
        ]);

        return $this->responseWithXmlType(response($response));
    }

    public function sendTestSMS()
    {
        $smsQueue = Enrollee::toSMS()->with('provider')->get();

        foreach ($smsQueue as $recipient) {
            $provider_name = optional($recipient->provider)->getProviderFullNameAttribute();

            if (null == $recipient->invite_sent_at) {
                //first go, make invite code:

                $recipient->invite_code     = rand(183, 982).substr(uniqid(), -3);
                $link                       = url("join/$recipient->invite_code");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 1;
                $recipient->save();

                $message = "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link";

                $this->client->account->messages->create(
                // the number we are sending to - Any phone number
                    $recipient->cell_phone,
                    [
                        'from' => $recipient->practice->outgoing_phone_number,
                        'body' => $message,
                    ]
                );
            } else {
                $sad_face_emoji = "\u{1F648}";

                $link                       = url("join/$recipient->invite_code");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 2;
                $recipient->save();

                $message = "Dr. $provider_name hasn’t heard from you regarding their new wellness program. $sad_face_emoji Please enroll here: $link";

                $this->client->account->messages->create(
                // the number we are sending to - Any phone number
                    $recipient->cell_phone,
                    [
                        'from' => $recipient->practice->outgoing_phone_number,
                        'body' => $message,
                    ]
                );
            }
        }
    }

    /**
     * Log sequence number and recording url of a dial call
     * The rest we can get from dialNumberStatusCallback.
     *
     * @param Request $request
     */
    private function logDialActionToDb(Request $request)
    {
        $input = $request->all();

        //CallSid in dial action url is the sid of the main call leg (the parent)
        $callSid = $input['CallSid'];

        $fields = [
            'call_sid' => $callSid,
        ];

        if ( ! empty($input['RecordingUrl'])) {
            $fields['dial_recording_url'] = $input['RecordingUrl'];
        }

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

            if ( ! empty($input['RecordingSid'])) {
                $fields['recording_sid'] = $input['RecordingSid'];
            }

            if ( ! empty($input['RecordingDuration'])) {
                $fields['recording_duration'] = $input['RecordingDuration'];
            }

            if ( ! empty($input['RecordingUrl'])) {
                $fields['recording_url'] = $input['RecordingUrl'];
            }

            if ( ! empty($input['SequenceNumber'])) {
                $fields['sequence_number'] = $input['SequenceNumber'];
            }

            // For the next properties, we append dial_ because we want to keep information from parent call as well

            if ( ! empty($input['CallSid'])) {
                $fields['dial_call_sid'] = $input['CallSid'];
            }

            if ( ! empty($input['CallDuration'])) {
                $fields['dial_call_duration'] = $input['CallDuration'];
            }

            if ( ! empty($input['CallStatus'])) {
                $fields['dial_call_status'] = $input['CallStatus'];
            }

            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                $fields
            );
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio log: '.$e->getMessage());
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
                $fields['call_duration'] = $input['CallDuration'];
            }

            if ( ! empty($input['InboundUserId'])) {
                $fields['inbound_user_id'] = $input['InboundUserId'];
            }

            if ( ! empty($input['OutboundUserId'])) {
                $fields['outbound_user_id'] = $input['OutboundUserId'];
            }

            if ( ! empty($input['IsUnlistedNumber'])) {
                $fields['is_unlisted_number'] = $input['IsUnlistedNumber'];
            }

            if ( ! empty($input['SequenceNumber'])) {
                $fields['sequence_number'] = $input['SequenceNumber'];
            }

            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                $fields
            );
        } catch (\Throwable $e) {
            \Log::critical('Exception while storing twilio log: '.$e->getMessage());
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
            \Log::critical('Exception while storing twilio raw log: '.$e->getMessage());
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
            $xml = new SimpleXMLElement('<'.$rootElement.'/>');
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

        return \Response::make($xml->asXML(), $status, $header);
    }

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }

    private function sendUnlistedNumberToSlack($input)
    {
        $userId         = $input['InboundUserId'];
        $unlistedNumber = $input['To'];
        sendSlackMessage(
            '#twilio-calls',
            "User [$userId] is trying to call a non-predefined phone number [$unlistedNumber]."
        );
    }
}
