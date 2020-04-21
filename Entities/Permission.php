<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Michalisantoniou6\Cerberus\CerberusPermission;

/**
 * CircleLinkHealth\Customer\Entities\Permission.
 *
 * @property int                                                                                 $id
 * @property string                                                                              $name
 * @property string|null                                                                         $display_name
 * @property string|null                                                                         $description
 * @property \Carbon\Carbon                                                                      $created_at
 * @property \Carbon\Carbon                                                                      $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection $roles
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereCreatedAt($value)
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereDescription($value)
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereDisplayName($value)
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereId($value)
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereName($value)
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\App\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $users
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission newModelQuery()
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission newQuery()
 * @method static                                                                              \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission query()
 * @property int|null                                                                            $roles_count
 * @property int|null                                                                            $users_count
 */
class Permission extends CerberusPermission
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_permissions';
}
