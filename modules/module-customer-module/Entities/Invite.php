<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\Invite.
 *
 * @property int                                           $id
 * @property int                                           $inviter_id
 * @property int|null                                      $role_id
 * @property string                                        $email
 * @property string|null                                   $subject
 * @property string|null                                   $message
 * @property string|null                                   $code
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \Carbon\Carbon|null                           $deleted_at
 * @property \CircleLinkHealth\Customer\Entities\User      $inviter
 * @property \CircleLinkHealth\Customer\Entities\Role|null $role
 * @method   static                                        bool|null forceDelete()
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Entities\Invite onlyTrashed()
 * @method   static                                        bool|null restore()
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereCode($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereCreatedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereDeletedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereEmail($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereInviterId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereMessage($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereRoleId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereSubject($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereUpdatedAt($value)
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Entities\Invite withTrashed()
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Entities\Invite withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite query()
 * @property int|null                                                                                    $revision_history_count
 */
class Invite extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'inviter_id',
        'role_id',
        'email',
        'subject',
        'message',
        'code',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id', 'ID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
