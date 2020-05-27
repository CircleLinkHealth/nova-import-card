<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Cache;
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
 * @property int|null $practice_id
 * @property string   $type
 */
class EnrollmentInvitationsBatch extends Model
{
    const MANUAL_INVITES_BATCH_TYPE = 'one-off_invitations';

    protected $fillable = [
        'practice_id',
        'type',
    ];

    public static function firstOrCreateAndRemember(int $practiceId, string $type, int $minutes = 2)
    {
        return Cache::remember("temp_{$practiceId}_{$type}_initial_batch", $minutes, function () use ($practiceId, $type) {
            return EnrollmentInvitationsBatch::firstOrCreate([
                'practice_id' => $practiceId,
                'type'        => $type,
            ]);
        });
    }

    public function invitationLinks()
    {
        return $this->hasMany(EnrollableInvitationLink::class);
    }

    /**
     * @return mixed
     */
    public static function manualInvitesBatch(int $practiceId)
    {
        return \Cache::remember("manual_invites_running_enrollment_batch_for_$practiceId", 2, function () use ($practiceId) {
            return EnrollmentInvitationsBatch::firstOrCreate([
                'practice_id' => $practiceId,
                'type'        => self::MANUAL_INVITES_BATCH_TYPE,
            ]);
        });
    }

    /**
     * @return EnrollmentInvitationsBatch|Model
     */
    public static function massInvitesBatch(int $practiceId, string $color)
    {
        return EnrollmentInvitationsBatch::create([
            'practice_id' => $practiceId,
            'type'        => $color,
        ]);
    }
}
