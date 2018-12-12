<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Represents a participant of a conference call.
 *
 * @property string $call_sid
 * @property string $account_sid
 * @property string $conference_sid
 * @property string $participant_number
 * @property string $status
 * @property integer $duration
 */
class TwilioConferenceCallParticipant extends BaseModel
{
    protected $fillable = ['call_sid', 'account_sid', 'conference_sid', 'participant_number', 'status', 'duration'];
    protected $table = 'twilio_conference_calls_participants';
}
