<?php

namespace App;

/**
 * Structured twilio call logs.
 * @package App
 *
 * @property string $call_sid Unique Identifier for a call
 * @property string $call_status One of: queued, ringing, in-progress, completed, busy, failed, no-answer
 * @property string $from The phone number, SIP address, Client identifier or SIM SID that made this Call
 * @property string $to The target call
 * @property string $inbound_user_id The user receiving the call
 * @property string $outbound_user_id The user making the call
 * @property integer $call_duration
 * @property string $direction inbound for inbound calls, outbound-api for calls initiated via the REST API or
 *     outbound-dial for calls initiated by a <Dial> verb.
 * @property string $recording_sid
 * @property integer $recording_duration
 * @property string $recording_url
 * @property string $sequence_number The order in which events are fired. Events are fired in order, but may not be
 *     received in order.
 *
 */
class TwilioCall extends BaseModel
{
    protected $table = 'twilio_calls';

    protected $fillable = [
        'call_sid',
        'call_status',
        'from',
        'to',
        'inbound_user_id',
        'outbound_user_id',
        'call_duration',
        'direction',
        'recording_sid',
        'recording_duration',
        'recording_url',
        'sequence_number',
    ];
}
