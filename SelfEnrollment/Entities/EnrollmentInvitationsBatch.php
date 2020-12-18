<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\SelfEnrollment\Entities;

use Cache;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EnrollmentInvitationsBatch.
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
