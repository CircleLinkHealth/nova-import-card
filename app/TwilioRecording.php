<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Represents a twilio voice recording.
 *
 * @property string                                                                                      $call_sid
 * @property string                                                                                      $account_sid
 * @property string                                                                                      $conference_sid  Only present in case of a recording of a conference
 * @property string                                                                                      $source          One of 'DialVerb' and 'Conference'
 * @property string                                                                                      $status          One of 'in-progress' and 'completed'
 * @property string                                                                                      $url
 * @property int                                                                                         $duration
 * @property int                                                                                         $id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereAccountSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereCallSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereConferenceSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereUrl($value)
 * @mixin \Eloquent
 *
 * @property int|null $revision_history_count
 */
class TwilioRecording extends BaseModel
{
    protected $fillable = ['call_sid', 'account_sid', 'conference_sid', 'source', 'status', 'url', 'duration'];
    protected $table    = 'twilio_recordings';
}
