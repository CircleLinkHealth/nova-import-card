<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Structured twilio call logs.
 *
 * @property string $call_sid           Unique Identifier for a call
 * @property string $call_status        One of: queued, ringing, in-progress, completed, busy, failed, no-answer
 * @property string $from               The phone number, SIP address, Client identifier or SIM SID that made this Call
 * @property string $to                 The target call
 * @property string $inbound_user_id    The user receiving the call
 * @property string $outbound_user_id   The user making the call
 * @property int    $call_duration
 * @property string $direction          inbound for inbound calls, outbound-api for calls initiated via the REST API or
 *                                      outbound-dial for calls initiated by a <Dial> verb.
 * @property string $recording_sid
 * @property int    $recording_duration
 * @property string $recording_url
 * @property string $sequence_number    The order in which events are fired. Events are fired in order, but may not be
 *                                      received in order.
 */
class TwilioCall extends BaseModel
{
    protected $fillable = [
        'application_sid',
        'account_sid',
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
        'dial_call_sid',
        'dial_call_duration',
        'dial_call_status',
    ];
    protected $table = 'twilio_calls';
}
