<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Illuminate\Database\Eloquent\Builder;

abstract class ChunksEloquentBuilderJob implements ChunksEloquentBuilder
{
    protected Builder $builder;

    protected int $limit;

    protected int $offset;

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setBuilder(int $offset, int $limit, Builder $builder): self
    {
        $this->builder = $builder
            ->offset($this->offset = $offset)
            ->limit($this->limit = $limit);

        return $this;
    }
}
