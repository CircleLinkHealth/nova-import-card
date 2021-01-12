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
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Michalisantoniou6\Cerberus\Contracts\CerberusSiteInterface;
use Michalisantoniou6\Cerberus\Traits\CerberusSiteTrait;

class CerberusSite extends Model implements CerberusSiteInterface
{
    use CerberusSiteTrait;

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
        $this->table = Config::get('cerberus.sites_table');
    }
}
