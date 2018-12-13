<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Structured twilio call logs.
 *
 * @property string $call_sid           Unique Identifier for a call session (note: a call session can have dial leg or
 *                                      conference leg, which have different ids)
 * @property string $call_status        One of: queued, ringing, in-progress, completed (status about the call session
 *                                      itself. To see if the call was answered or not, use dial_call_status)
 * @property string $from               The phone number, SIP address, Client identifier or SIM SID that made this Call
 * @property string $to                 The target call
 * @property string $inbound_user_id    The user receiving the call
 * @property string $outbound_user_id   The user making the call
 * @property int $call_duration      The total duration from the moment you press Call on the web site until the
 *                                      connection is closed.
 * @property string $direction          inbound for inbound calls, outbound-api for calls initiated via the REST API or
 *                                      outbound-dial for calls initiated by a <Dial> verb.
 * @property bool $in_conference      States whether the call is in conference mode
 * @property bool $is_unlisted_number States whether the phone number was manually entered on client side
 * @property int $dial_conference_duration The effective duration of a call from the moment the first participant
 *     answers until close.
 * @property int $dial_call_status   Read this value to see if the other party has picked up (queued, ringing,
 *                                      in-progress, completed, busy, failed, no-answer)
 * @property int $dial_call_sid      The session id of the call to the other party. Different from call_sid.
 *                                      call_sid is the Parent session. connection is closed.
 * @property string $dial_recording_sid
 * @property string $conference_sid
 * @property integer $conference_duration
 * @property integer $conference_status
 * @property string $conference_recording_sid
 * @property string $conference_friendly_name
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
        'sequence_number',
        'dial_call_sid',
        'dial_call_status',
        'dial_recording_sid',
        'conference_sid',
        'conference_duration',
        'conference_status',
        'conference_recording_sid',
        'conference_friendly_name',
        'in_conference',
        'is_unlisted_number',
        'dial_conference_duration',
    ];
    protected $table = 'twilio_calls';

    public $phi = ['from', 'to'];
}
