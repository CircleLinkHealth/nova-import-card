<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\Call.
 *
 * @property int                                                                                                             $id
 * @property string|null                                                                                                     $type
 * @property int|null                                                                                                        $note_id
 * @property string|null                                                                                                     $service
 * @property string|null                                                                                                     $status
 * @property string|null                                                                                                     $inbound_phone_number
 * @property string|null                                                                                                     $outbound_phone_number
 * @property int                                                                                                             $inbound_cpm_id
 * @property int|null                                                                                                        $outbound_cpm_id
 * @property int|null                                                                                                        $call_time
 * @property \Illuminate\Support\Carbon|null                                                                                 $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $updated_at
 * @property int|null                                                                                                        $is_cpm_outbound
 * @property string|null                                                                                                     $window_start
 * @property string|null                                                                                                     $window_end
 * @property string|null                                                                                                     $scheduled_date
 * @property string|null                                                                                                     $called_date
 * @property string|null                                                                                                     $attempt_note
 * @property string|null                                                                                                     $scheduler
 * @property int|null                                                                                                        $is_manual
 * @property string|null                                                                                                     $sub_type
 * @property int                                                                                                             $asap
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection                      $attestedProblems
 * @property int|null                                                                                                        $attested_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmCallAlert|null                                                       $cpmCallAlert
 * @property mixed                                                                                                           $is_from_care_center
 * @property \CircleLinkHealth\Customer\Entities\User                                                                        $inboundUser
 * @property \CircleLinkHealth\SharedModels\Entities\Note|null                                                               $note
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                                   $outboundUser
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @property int|null                                                                                                        $revision_history_count
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                                   $schedulerUser
 * @property \CircleLinkHealth\SharedModels\Entities\VoiceCall[]|\Illuminate\Database\Eloquent\Collection                    $voiceCalls
 * @property int|null                                                                                                        $voice_calls_count
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call calledLastThreeMonths()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdInMonth(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdOn(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdOnIfNotNull(?\Carbon\Carbon $date = null, $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdThisMonth(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdToday(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call createdYesterday(string $field = 'created_at')
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call filter(\CircleLinkHealth\Core\Filters\QueryFilters $filters)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call newModelQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call newQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call ofMonth(\Carbon\Carbon $monthYear)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call ofStatus($status)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call query()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call scheduled()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|Call unassigned()
 * @mixin \Eloquent
 */
class Call extends \CircleLinkHealth\SharedModels\Entities\Call
{
    //The only purpose of this class is because it throws an exception
    //when redirecting to /redirect-mark-read for pre-existing notifications with
    //relation to App\Call
}
