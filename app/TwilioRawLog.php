<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Unstructured twilio logs (raw).
 *
 * @property string                                                                                      $call_sid
 * @property string                                                                                      $application_sid
 * @property string                                                                                      $account_sid
 * @property string                                                                                      $call_status
 * @property string                                                                                      $log
 * @property int                                                                                         $id
 * @property string|null                                                                                 $type
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereAccountSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereApplicationSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCallSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int|null $revision_history_count
 */
class TwilioRawLog extends BaseModel
{
    protected $fillable = ['call_sid', 'call_status', 'application_sid', 'account_sid', 'log', 'type'];
    protected $table    = 'twilio_raw_logs';
}
