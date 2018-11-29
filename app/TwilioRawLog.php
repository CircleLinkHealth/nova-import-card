<?php

namespace App;

class TwilioRawLog extends BaseModel
{
    protected $table = 'twilio_raw_logs';

    protected $fillable = ['sid', 'call_status', 'log'];
}
