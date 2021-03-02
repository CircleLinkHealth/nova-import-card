<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

/**
 * CircleLinkHealth\Customer\Entities\Appointment.
 *
 * @property int                                           $id
 * @property int                                           $patient_id
 * @property int                                           $author_id
 * @property int|null                                      $provider_id
 * @property string                                        $date
 * @property string                                        $time
 * @property string                                        $status
 * @property string                                        $comment
 * @property int                                           $was_completed
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property string                                        $type
 * @property \CircleLinkHealth\Customer\Entities\User      $author
 * @property \CircleLinkHealth\Customer\Entities\User      $patient
 * @property \CircleLinkHealth\Customer\Entities\User|null $provider
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereAuthorId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereComment($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereCreatedAt($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereDate($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment wherePatientId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereProviderId($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereStatus($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereTime($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereType($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereUpdatedAt($value)
 * @method static                                        \Illuminate\Database\Eloquent\Builder|\App\Appointment whereWasCompleted($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment query()
 * @property int|null                                                                                    $revision_history_count
 */
class Appointment extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'patient_id',
        'author_id',
        'provider_id',
        'was_completed',
        'type',
        'date',
        'time',
        'comment',
        'created_at',
        'updated_at',
    ];
    protected $table = 'appointments';

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
