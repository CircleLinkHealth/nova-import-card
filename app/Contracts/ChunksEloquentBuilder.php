<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ChunksEloquentBuilder
{
    public function setBuilder(int $offset, int $limit, Builder $builder): self;
}
