<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

/**
 * CircleLinkHealth\SharedModels\Entities\TwilioDebuggerLog.
 *
 * @property int                                                                                         $id
 * @property string                                                                                      $sid
 * @property string                                                                                      $account_sid
 * @property string                                                                                      $parent_account_sid
 * @property string                                                                                      $level
 * @property mixed                                                                                       $payload
 * @property \Illuminate\Support\Carbon                                                                  $event_timestamp
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\TwilioDebuggerLog newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\TwilioDebuggerLog newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\TwilioDebuggerLog query()
 * @mixin \Eloquent
 */
class TwilioDebuggerLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $dates = [
        'event_timestamp',
    ];
    protected $fillable = [
        'sid',
        'account_sid',
        'parent_account_sid',
        'level',
        'payload',
        'event_timestamp',
    ];

    protected $table = 'twilio_debugger_logs';
}
