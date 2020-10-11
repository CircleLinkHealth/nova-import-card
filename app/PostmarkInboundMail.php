<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PostmarkInboundMail.
 *
 * @property int                             $id
 * @property mixed                           $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\PostmarkInboundMail newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\PostmarkInboundMail newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\PostmarkInboundMail query()
 * @mixin \Eloquent
 * @property string|null $from
 * @property string|null $to
 * @property string|null $body
 */
class PostmarkInboundMail extends Model
{
    protected $fillable = [
        'data',
    ];
    
    protected $table = 'postmark_inbound_mail';
}
