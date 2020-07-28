<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\Customer\Entities\CarePerson.
 *
 * @property int                                      $id
 * @property int                                      $alert
 * @property int                                      $user_id
 * @property int                                      $member_user_id
 * @property string                                   $type
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereMemberUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUserId($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson query()
 *
 * @property int|null $revision_history_count
 */
class CarePerson extends BaseModel
{
    const BILLING_PROVIDER                = 'billing_provider';
    const EXTERNAL                        = 'external';
    const IN_ADDITION_TO_BILLING_PROVIDER = 'in_addition_to_billing_provider';
    const INSTEAD_OF_BILLING_PROVIDER     = 'instead_of_billing_provider';
    const LEAD_CONTACT                    = 'lead_contact';
    const MEMBER                          = 'member';

    const REGULAR_DOCTOR = 'regular_doctor';
    const SEND_ALERT_TO  = 'send_alert_to';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'member_user_id',
        'type',
        'alert',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patient_care_team_members';

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'member_user_id', 'id');
    }
}
