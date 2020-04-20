<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\NurseInvoices\Entities\Dispute.
 *
 * @property int                                           $id
 * @property string                                        $disputable_type
 * @property int                                           $disputable_id
 * @property string                                        $reason
 * @property string|null                                   $resolved_at
 * @property int|null                                      $resolved_by
 * @property int                                           $user_id
 * @property string|null                                   $resolution_note
 * @property \Illuminate\Support\Carbon|null               $created_at
 * @property \Illuminate\Support\Carbon|null               $updated_at
 * @property bool                                          $is_resolved
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $disputable
 * @property \CircleLinkHealth\Customer\Entities\User|null $resolver
 * @property \CircleLinkHealth\Customer\Entities\User      $user
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereDisputableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereDisputableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereIsResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereResolutionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereResolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\Dispute whereUserId($value)
 * @mixin \Eloquent
 */
class Dispute extends Model
{
    protected $casts = [
        'is_resolved' => 'boolean',
    ];
    protected $fillable = [
        'user_id',
        'reason',
        'resolved_at',
        'resolved_by',
        'resolution_note',
        'is_resolved',
    ];

    public function disputable()
    {
        return $this->morphTo();
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
