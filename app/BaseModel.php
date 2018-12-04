<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * App\BaseModel
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    use LadaCacheTrait,
        RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Add any attributes that are PHI here.
     * [What is PHI](https://www.hipaa.com/hipaa-protected-health-information-what-does-phi-include/)
     *
     * @var array
     */
    public $phi = [];
}
