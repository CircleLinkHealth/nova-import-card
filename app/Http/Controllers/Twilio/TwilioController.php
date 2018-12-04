<?php

namespace App\Http\Controllers\Twilio;

use App\Enrollee;
use App\Http\Controllers\Controller;
use App\TwilioCall;
use App\TwilioRawLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Twilio\Exceptions\TwimlException;
use Twilio\Jwt\ClientToken;
use Twilio\Twiml;
use Twilio\Rest\Client;

class TwilioController extends Controller
{
    
    const CLIENT_ANONYMOUS = 'client:Anonymous';
    
    private $capability;
    private $token;
    
    public function __construct()
    {
        $this->capability = new ClientToken(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability->allowClientOutgoing(config('services.twilio.twiml-app-sid'));
        $this->token = $this->capability->generateToken();
    }
    
    public function obtainToken()
    {
        return response()->json(['token' => $this->token]);
    }
    
    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Twilio\Exceptions\TwimlException
     */
    public function placeCall(Request $request)
    {
        $this->logRawToDb($request, 'init');
        
        $input = $request->all();
        
        if (empty($input['From']) || $input['From'] === TwilioController::CLIENT_ANONYMOUS) {
            $input['From'] = config('services.twilio')['from'];
        }
        
        $validation  = \Validator::make($input, [
            //could be the practice outgoing phone number (in case of enrollment)
            'From'             => 'required|phone:AUTO,US',
            'To'               => 'required|phone:AUTO,US',
            'InboundUserId'    => 'required',
            'OutboundUserId'   => 'required',
            'IsUnlistedNumber' => '',
        ]);
        
        if ($validation->fails()) {
            return $this->responseWithXmlData($validation->errors()->all(), 400);
        }
        
        $this->logToDb($request, $input);
        
        $response = new Twiml();
        $dial     = $response->dial($input['To'], [
            //action url will tell us the duration of this call and the status of it when it ends
            'action'   => route('twilio.call.dial.status'),
            'callerId' => $input['From'],
        ]);
        
        /**
         * $dial->number($input['To'], [
         * 'statusCallback' => route('twilio.call.number.status'),
         * 'statusCallbackEvent' => 'initiated ringing answered completed'
         * ]);
         */
        
        return $this->responseWithXmlType(response($response));
    }
    
    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
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
        
        return \Response::make($xml->asXML(), $status, $header);
    }
    
    public function sendTestSMS()
    {
        
        $client = new Client($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);
        
        $smsQueue = Enrollee::toSMS()->with('provider')->get();
        
        foreach ($smsQueue as $recipient) {
            $provider_name =
                
                
                optional($recipient->provider)->getProviderFullNameAttribute();
            
            if ($recipient->invite_sent_at == null) {
                //first go, make invite code:
                
                $recipient->invite_code     = rand(183, 982) . substr(uniqid(), -3);
                $link                       = url("join/$recipient->invite_code");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 1;
                $recipient->save();
                
                $message = "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link";
                
                $client->account->messages->create(
                
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
                
                $client->account->messages->create(
                
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
     * This function is called from Twilio (Dial Action URL - see placeCall above)
     * When the call ends, this handler is called (different from callStatusCallback below)
     * This handler decides what happens next:
     * We simply log the status and duration and hang up.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function dialStatusCallback(Request $request)
    {
        $this->logRawToDb($request, 'dial-status');
        $this->logDialToDb($request);
        
        try {
            $response = new Twiml();
            $response->hangup();
            
            return $this->responseWithXmlType(response($response));
        } catch (TwimlException $e) {
            return $this->responseWithXmlData(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * This function is called from Twilio (status callback)
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request from Twilio and creates or updates any existing calls (using call sid)
     *
     * @param Request $request
     */
    public function callStatusCallback(Request $request)
    {
        $this->logRawToDb($request);
        $this->logToDb($request);
    }
    
    private function logToDb(Request $request, $input = null)
    {
        try {
            
            if ( ! $input) {
                $input = $request->all();
            }
            
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
            
            if ( ! empty($input['Direction'])) {
                $fields['direction'] = $input['Direction'];
            }
            
            //only present in 'completed' status event
            if ( ! empty($input['CallDuration'])) {
                $fields['call_duration'] = $input['CallDuration'];
            }
            
            if ( ! empty($input['From']) && $input['From'] != TwilioController::CLIENT_ANONYMOUS) {
                $fields['from'] = $input['From'];
            }
            
            if ( ! empty($input['To'])) {
                $fields['to'] = $input['To'];
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
            
            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                $fields
            );
            
        } catch (\Throwable $e) {
            \Log::critical("Exception while storing twilio log: " . $e->getMessage());
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
                                     'type'            => $type == null
                                         ? $request->get('CallbackSource', null)
                                         : $type
                                 ]);
        } catch (\Throwable $e) {
            \Log::critical("Exception while storing twilio raw log: " . $e->getMessage());
        }
    }
    
    private function logDialToDb(Request $request)
    {
        $input = $request->all();
        
        $callSid = $input['CallSid'];
        
        $fields = [
            'call_sid' => $callSid,
        ];
        
        if ( ! empty($input['CallStatus'])) {
            $fields['call_status'] = $input['CallStatus'];
        }
        
        if ( ! empty($input['DialCallSid'])) {
            $fields['dial_call_sid'] = $input['DialCallSid'];
        }
        
        if ( ! empty($input['DialCallDuration'])) {
            $fields['dial_call_duration'] = $input['DialCallDuration'];
        }
        
        if ( ! empty($input['DialCallStatus'])) {
            $fields['dial_call_status'] = $input['DialCallStatus'];
        }
        
        if ( ! empty($input['RecordingUrl'])) {
            $fields['dial_recording_url'] = $input['RecordingUrl'];
        }
        
        TwilioCall::updateOrCreate(
            ['call_sid' => $callSid],
            $fields
        );
    }
}
