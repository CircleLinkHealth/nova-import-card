<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Contracts\AttachableToNotification;
use App\Contracts\RelatesToActivity;
use App\Traits\ActivityRelatable;
use App\Traits\NotificationAttachable;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Addendum.
 *
 * @property int                                           $id
 * @property string                                        $addendumable_type
 * @property int                                           $addendumable_id
 * @property int                                           $author_user_id
 * @property string                                        $body
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $addendumable
 * @property \CircleLinkHealth\Customer\Entities\User      $author
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableType($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAuthorUserId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereBody($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereCreatedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum newModelQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum newQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum query()
 * @property int|null                                                                                                        $revision_history_count
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 */
class Addendum extends \CircleLinkHealth\Core\Entities\BaseModel implements RelatesToActivity, AttachableToNotification
{
    use ActivityRelatable;
    use NotificationAttachable;

    protected $fillable = [
        'addendumable_type',
        'addendumable_id',
        'author_user_id',
        'body',
    ];

    /**
     * Get all of the owning addendumable models.
     */
    public function addendumable()
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /**
     * Return a call object.
     *
     * @return mixed
     */
    public function getActivities()
    {
        return Call::where('note_id', $this->addendumable_id)
            ->where('type', 'addendum')
            ->where('outbound_cpm_id', auth()->id());
    }

    /**
     * @return mixed
     */
    public function markAsReadInNotifications()
    {
        $addendumAuthor = $this->author_user_id;

        return $this->addendumable->addendums()
            ->where('author_user_id', $addendumAuthor)
            ->get();
    }
}
