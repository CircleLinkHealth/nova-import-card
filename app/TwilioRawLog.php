<?php

namespace App;

/**
 * Unstructured twilio logs (raw)
 * @package App
 *
 * @property string $call_sid
 * @property string $application_sid
 * @property string $account_sid
 * @property string $call_status
 * @property string $log
 */
class TwilioRawLog extends BaseModel
{
    protected $table = 'twilio_raw_logs';

    protected $fillable = ['call_sid', 'application_sid', 'account_sid', 'call_status', 'log'];
}
