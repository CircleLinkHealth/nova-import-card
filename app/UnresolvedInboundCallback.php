<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UnresolvedInboundCallback.
 *
 * @property int                             $id
 * @property int                             $postmark_rec_id
 * @property int                             $call_id
 * @property mixed                           $suggestions
 * @property int                             $resolved_manually
 * @property string|null                     $issue_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedInboundCallback newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedInboundCallback newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|UnresolvedInboundCallback query()
 * @mixin \Eloquent
 */
class UnresolvedInboundCallback extends Model
{
    protected $table = 'unresolved_postmark_inbound_callbacks';
    
    protected $fillable = [
        'postmark_rec_id',
        'suggestions',
    ];

    public function inboundPostmark()
    {
        return $this->belongsTo(PostmarkInboundMail::class, 'postmark_rec_id');
    }
}
