<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\CpmCallAlert.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $call_id
 * @property int                                                                                         $resolved
 * @property string                                                                                      $comment
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CpmCallAlert newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CpmCallAlert newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CpmCallAlert query()
 * @mixin \Eloquent
 * @property \App\Call|null $cpmCall
 */
class CpmCallAlert extends BaseModel
{
    protected $casts = [
        'resolved' => 'boolean',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'call_id',
        'resolved',
    ];

    public function cpmCall()
    {
        return $this->belongsTo(Call::class, 'call_id', 'id');
    }
}
