<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback.
 *
 * @property int                                                         $id
 * @property int                                                         $postmark_id
 * @property int|null                                                    $user_id
 * @property string|null                                                 $deleted_at
 * @property mixed                                                       $suggestions
 * @property \Illuminate\Support\Carbon|null                             $created_at
 * @property \Illuminate\Support\Carbon|null                             $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail $inboundPostmark
 * @method static                                                      \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback newModelQuery()
 * @method static                                                      \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback newQuery()
 * @method static                                                      \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback query()
 * @mixin \Eloquent
 * @property mixed      $unresolved_reasons
 * @property mixed|null $unresolved_reason
 * @property int        $manually_resolved
 * @method static     \Illuminate\Database\Query\Builder|UnresolvedPostmarkCallback onlyTrashed()
 * @method static     \Illuminate\Database\Query\Builder|UnresolvedPostmarkCallback withTrashed()
 * @method static     \Illuminate\Database\Query\Builder|UnresolvedPostmarkCallback withoutTrashed()
 */
class UnresolvedPostmarkCallback extends Model
{
    use SoftDeletes;

    protected $casts = [
        'suggestions'       => 'array',
        'manually_resolved' => 'boolean',
    ];

    protected $fillable = [
        'postmark_id',
        'user_id',
        'unresolved_reason',
        'suggestions',
    ];

    public function inboundPostmark()
    {
        return $this->belongsTo(PostmarkInboundMail::class, 'postmark_id');
    }
}
