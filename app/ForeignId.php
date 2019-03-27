<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * These are IDs from third party systems.
 *
 * Example use:
 * XYZ CCD Vendor uses our API to submit CCDs and receive back reports and wants their system's id returned in the
 * response.
 *
 * Class ForeignId
 *
 * @property int            $id
 * @property int            $user_id
 * @property int|null       $location_id
 * @property string         $foreign_id
 * @property string         $system
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User      $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereUserId($value)
 * @mixin \Eloquent
 */
class ForeignId extends \CircleLinkHealth\Core\Entities\BaseModel implements Transformable
{
    use TransformableTrait;

    //Define systems here
    const APRIMA = 'aprima';
    const ATHENA = 'athena';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
