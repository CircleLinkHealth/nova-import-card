<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SamlSp\Entities\SamlUser.
 *
 * @property int                                                                                         $id
 * @property string                                                                                      $idp
 * @property string                                                                                      $idp_user_id
 * @property int                                                                                         $cpm_user_id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property User                                                                                        $cpmUser
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|SamlUser newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|SamlUser newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|SamlUser query()
 * @mixin \Eloquent
 */
class SamlUser extends BaseModel
{
    protected $fillable = [
        'idp',
        'idp_user_id',
        'cpm_user_id',
    ];
    protected $table = 'saml_users';

    public function cpmUser()
    {
        return $this->belongsTo(User::class, 'cpm_user_id', 'id');
    }
}
