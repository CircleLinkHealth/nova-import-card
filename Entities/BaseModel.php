<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * CircleLinkHealth\Core\Entities\BaseModel.
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    use LadaCacheTrait,
        RevisionableTrait;

    /**
     * Add any attributes that are PHI here.
     * [What is PHI](https://www.hipaa.com/hipaa-protected-health-information-what-does-phi-include/).
     *
     * @var array
     */
    public $phi = [];

    protected $revisionCreationsEnabled = true;
}
