<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Michalisantoniou6\Cerberus;

/*
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Permission[] $perms
 * @property-read int|null $perms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereUpdatedAt($value)
 * @mixin \Eloquent
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Michalisantoniou6\Cerberus\Contracts\CerberusRoleInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusRoleTrait;

/**
 * Michalisantoniou6\Cerberus\CerberusRole.
 *
 * @property int                                                                                       $id
 * @property string                                                                                    $name
 * @property string|null                                                                               $display_name
 * @property string|null                                                                               $description
 * @property \Illuminate\Support\Carbon|null                                                           $created_at
 * @property \Illuminate\Support\Carbon|null                                                           $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection $perms
 * @property int|null                                                                                  $perms_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection       $users
 * @property int|null                                                                                  $users_count
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole newModelQuery()
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole newQuery()
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole query()
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereCreatedAt($value)
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereDescription($value)
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereDisplayName($value)
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereId($value)
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereName($value)
 * @method static                                                                                    \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusRole whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CerberusRole extends Model implements CerberusRoleInterface
{
    use CerberusRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('cerberus.roles_table');
    }
}
