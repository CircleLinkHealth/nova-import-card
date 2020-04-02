<?php namespace Michalisantoniou6\Cerberus;

/**
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Michalisantoniou6\Cerberus\CerberusPermission whereUpdatedAt($value)
 * @mixin \Eloquent
 */

use Michalisantoniou6\Cerberus\Contracts\CerberusPermissionInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusPermissionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class CerberusPermission extends Model implements CerberusPermissionInterface
{
    use CerberusPermissionTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('cerberus.permissions_table');
    }

}
