<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models;

use App\User;

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
 * @property \App\User                                     $author
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAuthorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Addendum extends \App\BaseModel
{
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

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
