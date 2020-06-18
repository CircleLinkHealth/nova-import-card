<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\OutgoingSms.
 *
 * @property int                             $id
 * @property int                             $sender_user_id
 * @property string                          $receiver_phone_number
 * @property string                          $message
 * @property string                          $status
 * @property string                          $status_details
 * @property string                          $sid
 * @property string                          $account_sid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutgoingSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutgoingSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OutgoingSms query()
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 */
class OutgoingSms extends BaseModel
{
    protected $fillable = [
        'sender_user_id',
        'receiver_phone_number',
        'message',
        'status',
        'status_details',
        'sid',
        'account_sid',
    ];
}
