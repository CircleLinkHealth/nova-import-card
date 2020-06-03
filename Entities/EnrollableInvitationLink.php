<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\EnrollableInvitationLink;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink.
 *
 * @property int                                                                    $id
 * @property int                                                                    $invitationable_id
 * @property string                                                                 $invitationable_type
 * @property string                                                                 $link_token
 * @property int                                                                    $manually_expired
 * @property \Illuminate\Support\Carbon|null                                        $created_at
 * @property \Illuminate\Support\Carbon|null                                        $updated_at
 * @property \CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo $enrollableInfoRequest
 * @property \App\EnrollableInvitationLink                                          $enrollmentInvitationLinks
 * @property \App\EnrollableInvitationLink                                          $invitationable
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink newModelQuery()
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink newQuery()
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink query()
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereCreatedAt($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereId($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereInvitationableId($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereInvitationableType($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereLinkToken($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereManuallyExpired($value)
 * @method   static                                                                 \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string                                                                                      $url
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrollableInvitationLink whereUrl($value)
 * @property string|null                                                                                 $button_color
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property string|null                                                                                 $short_url
 * @property int                                                                                         $batch_id
 */
class EnrollableInvitationLink extends BaseModel
{
    protected $fillable = [
        'invitationable_id',
        'invitationable_type',
        'link_token',
        'url',
        'manually_expired',
        'button_color',
        'batch_id',
    ];

    protected $table = 'enrollables_invitation_links';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function invitationable()
    {
        return $this->morphTo();
    }
}
