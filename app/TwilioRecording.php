<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Represents a twilio voice recording.
 *
 * @property string $call_sid
 * @property string $account_sid
 * @property string $conference_sid Only present in case of a recording of a conference
 * @property string $source One of 'DialVerb' and 'Conference'
 * @property string $status One of 'in-progress' and 'completed'
 * @property string $url
 * @property integer $duration
 */
class TwilioRecording extends BaseModel
{
    protected $fillable = ['call_sid', 'account_sid', 'conference_sid', 'source', 'status', 'url', 'duration'];
    protected $table = 'twilio_recordings';
}
