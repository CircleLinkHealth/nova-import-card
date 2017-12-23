<?php

namespace App;


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