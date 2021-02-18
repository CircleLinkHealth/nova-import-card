<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Entities;

use CircleLinkHealth\SelfEnrollment\EnrollableInvitationLink\EnrollableInvitationLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class EnrollmentInvitationsBatch.
 *
 * @property int                                                                 $id
 * @property int|null                                                            $practice_id
 * @property string                                                              $type
 * @property \Illuminate\Support\Carbon|null                                     $created_at
 * @property \Illuminate\Support\Carbon|null                                     $updated_at
 * @property EnrollableInvitationLink[]|\Illuminate\Database\Eloquent\Collection $invitationLinks
 * @property int|null                                                            $invitation_links_count
 * @method   static                                                              \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationsBatch newModelQuery()
 * @method   static                                                              \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationsBatch newQuery()
 * @method   static                                                              \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationsBatch query()
 * @mixin \Eloquent
 */
class EnrollmentInvitationsBatch extends Model
{
    const MANUAL_INVITES_BATCH_TYPE = 'one-off_invitations';
    /**
     * Used in the "type" field to help Users visualize invitations sent per hour.
     */
    const TYPE_FIELD_DATE_HUMAN_FORMAT = 'm/d/Y hA';

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
}
