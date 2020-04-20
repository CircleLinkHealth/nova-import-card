<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use CircleLinkHealth\Core\Traits\ProtectsPhi;
use CircleLinkHealth\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Core\Entities\BaseModel.
 *
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel query()
 * @property int|null $revision_history_count
 */
class BaseModel extends Model
{
    use ProtectsPhi;
    use RevisionableTrait;

    /**
     * Add any attributes that are PHI here.
     * [What is PHI](https://www.hipaa.com/hipaa-protected-health-information-what-does-phi-include/).
     *
     * @var array
     */
    public $phi = [];

    protected $revisionCreationsEnabled = true;
}
