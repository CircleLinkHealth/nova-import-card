<?php

namespace App;

use CircleLinkHealth\Core\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class EnrolleeView extends Model
{
    use Filterable;

    protected $table = 'enrollees_view';

}
