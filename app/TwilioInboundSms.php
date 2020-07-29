<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TwilioInboundSms.
 *
 * @property int                             $id
 * @property mixed                           $data
 * @property string|null                     $from
 * @property string|null                     $to
 * @property string|null                     $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioInboundSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioInboundSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioInboundSms query()
 * @mixin \Eloquent
 */
class TwilioInboundSms extends Model
{
    protected $fillable = [
        'data',
    ];
    protected $table = 'twilio_inbound_sms';
}
