<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use CircleLinkHealth\Core\Traits\ProtectsPhi;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Core\Entities\SqlViewModel.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SqlViewModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SqlViewModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\SqlViewModel query()
 * @mixin \Eloquent
 */
class SqlViewModel extends Model
{
//    use ProtectsPhi;

    public $phi = [];
}
