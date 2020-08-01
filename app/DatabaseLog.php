<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use danielme85\LaravelLogToDB\Models\LogToDbCreateObject;
use Illuminate\Database\Eloquent\Model;

/**
 * App\DatabaseLog.
 *
 * @property int                             $id
 * @property string|null                     $message
 * @property string|null                     $channel
 * @property int                             $level
 * @property string                          $level_name
 * @property int                             $unix_time
 * @property string|null                     $datetime
 * @property array|null                      $context
 * @property array|null                      $extra
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseLog query()
 * @mixin \Eloquent
 */
class DatabaseLog extends Model
{
    use LogToDbCreateObject;
    protected $connection = 'mysql';

    protected $table = 'log';
}
