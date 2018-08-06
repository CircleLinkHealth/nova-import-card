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
}