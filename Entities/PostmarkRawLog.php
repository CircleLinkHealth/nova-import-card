<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

/**
 * CircleLinkHealth\Core\Entities\SendGridRawLog.
 *
 * @property int                                                                                         $id
 * @property mixed|null                                                                                  $events
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SendGridRawLog newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SendGridRawLog newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SendGridRawLog query()
 * @mixin \Eloquent
 */
class PostmarkRawLog extends BaseModel
{
    protected $casts = [
        'event' => 'array',
    ];

    protected $fillable = [
        'event',
    ];
    protected $table = 'postmark_raw_logs';
}
