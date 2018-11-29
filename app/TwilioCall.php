<?php

namespace App;

class TwilioCall extends BaseModel
{
    protected $table = 'twilio_calls';

    protected $fillable = ['call_sid', 'call_status', 'from', 'to', 'inbound_user_id', 'outbound_user_id', 'call_duration', 'duration'];
}
