<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Represents a participant of a conference call.
 *
 * @property string                                                                                      $call_sid
 * @property string                                                                                      $account_sid
 * @property string                                                                                      $conference_sid
 * @property string                                                                                      $participant_number
 * @property string                                                                                      $status
 * @property int                                                                                         $duration
 * @property int                                                                                         $id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereAccountSid($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereCallSid($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereConferenceSid($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereDuration($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereParticipantNumber($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereStatus($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $revision_history_count
 */
class TwilioConferenceCallParticipant extends BaseModel
{
    public $phi         = ['participant_number'];
    protected $fillable = ['call_sid', 'account_sid', 'conference_sid', 'participant_number', 'status', 'duration'];
    protected $table    = 'twilio_conference_calls_participants';
}
