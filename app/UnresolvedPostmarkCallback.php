<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UnresolvedPostmarkCallback.
 *
 * @property int                             $id
 * @property int                             $postmark_id
 * @property int|null                        $user_id
 * @property string|null                     $deleted_at
 * @property mixed                           $suggestions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\PostmarkInboundMail        $inboundPostmark
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedPostmarkCallback query()
 * @mixin \Eloquent
 * @property mixed      $unresolved_reasons
 * @property mixed|null $unresolved_reason
 */
class UnresolvedPostmarkCallback extends Model
{
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
