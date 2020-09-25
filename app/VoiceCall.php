<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\VoiceCall.
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model                                               $voiceCallable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VoiceCall newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VoiceCall newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VoiceCall query()
 * @mixin \Eloquent
 *
 * @property int                             $id
 * @property int|null                        $call_id
 * @property int                             $voice_callable_id
 * @property string                          $voice_callable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int                             $alert_level
 * @property \App\Call|null                  $cpmCall
 */
class VoiceCall extends BaseModel
{
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'call_id',
        'voice_callable_id',
        'voice_callable_type',
    ];

    public function cpmCall()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }

    public function voiceCallable()
    {
        return $this->morphTo();
    }
}
