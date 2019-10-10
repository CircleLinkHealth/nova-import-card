<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use CircleLinkHealth\Core\Traits\ProtectsPhi;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * CircleLinkHealth\Core\Entities\BaseModel.
 *
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel query()
 * @property-read int|null $revision_history_count
 */
class BaseModel extends Model
{
    use RevisionableTrait;
    use ProtectsPhi;

    /**
     * Add any attributes that are PHI here.
     * [What is PHI](https://www.hipaa.com/hipaa-protected-health-information-what-does-phi-include/).
     *
     * @var array
     */
    public $phi = [];

    protected $revisionCreationsEnabled = true;
}
