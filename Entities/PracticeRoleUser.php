<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Class PracticeRoleUser.
 *
 * @property int                                                                            $program_id
 * @property int                                                                            $user_id
 * @property int                                                                            $role_id
 * @property int|null                                                                       $has_admin_rights
 * @property int|null                                                                       $send_billing_reports
 * @property \Illuminate\Support\Carbon|null                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereHasAdminRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereSendBillingReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereUserId($value)
 * @mixin \Eloquent
 * @property-read int|null $revision_history_count
 * @property int $key_id
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereKeyId($value)
 */
class PracticeRoleUser extends BaseModel
{
    protected $primaryKey = 'key_id';

    protected $fillable = [
        'program_id',
        'user_id',
        'role_id',
    ];
    protected $table = 'practice_role_user';
}
