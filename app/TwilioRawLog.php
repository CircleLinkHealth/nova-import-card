<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Unstructured twilio logs (raw).
 *
 * @property string $call_sid
 * @property string $application_sid
 * @property string $account_sid
 * @property string $call_status
 * @property string $log
 */
class TwilioRawLog extends BaseModel
{
    protected $fillable = ['call_sid', 'call_status', 'application_sid', 'account_sid', 'log', 'type'];
    protected $table    = 'twilio_raw_logs';
}
