<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use Illuminate\Database\Eloquent\Model;

/**
 * App\EnrollmentInvitationsBatch.
 *
 * @property int                                                                                                                     $id
 * @property \Illuminate\Support\Carbon|null                                                                                         $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                         $updated_at
 * @property \CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink[]|\Illuminate\Database\Eloquent\Collection $invitationLinks
 * @property int|null                                                                                                                $invitation_links_count
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|\App\EnrollmentInvitationsBatch newModelQuery()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|\App\EnrollmentInvitationsBatch newQuery()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|\App\EnrollmentInvitationsBatch query()
 * @mixin \Eloquent
 */
class EnrollmentInvitationsBatch extends Model
{
    const MANUAL_INVITES_BATCH_TYPE = 'one-off_invitations';

    protected $fillable = [
        'practice_id',
        'type',
    ];

    public function invitationLinks()
    {
        return $this->hasMany(EnrollableInvitationLink::class);
    }

    public static function manualInvitesBatch(int $practiceId)
    {
        return EnrollmentInvitationsBatch::firstOrCreate([
            'practice_id' => $practiceId,
            'type'        => self::MANUAL_INVITES_BATCH_TYPE,
        ]);
    }
}
