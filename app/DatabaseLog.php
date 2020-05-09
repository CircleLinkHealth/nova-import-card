<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use danielme85\LaravelLogToDB\Models\LogToDbCreateObject;
use Illuminate\Database\Eloquent\Model;

class DatabaseLog extends Model
{
    use LogToDbCreateObject;
    protected $connection = 'mysql';

    protected $table = 'log';
}
