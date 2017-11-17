<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/17/2017
 * Time: 3:54 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseNotification extends \Illuminate\Notifications\DatabaseNotification
{
    protected $dates = [
        'read_at'
    ];

    /**
     * Get the attachment that was send with this notification.
     */
    public function attachment()
    {
        return $this->morphTo();
    }

    /**
     * Get the note that was attached to this notification.
     *
     * @todo: fix eager loading
     * @return BelongsTo
     */
    public function note() {
        return $this->belongsTo(Note::class, 'attachment_id')
            ->where('attachment_type', '=', Note::class);
    }

    /**
     * Get the user this notification belongs to.
     * @todo: fix eager loading
     * @return BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class, 'notifiable_id')
                    ->where('notifiable_type', '=', User::class);
    }
}