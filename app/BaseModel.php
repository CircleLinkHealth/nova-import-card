<?php

namespace App;

use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoinTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\BaseModel
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    use \Spiritix\LadaCache\Database\LadaCacheTrait;
}