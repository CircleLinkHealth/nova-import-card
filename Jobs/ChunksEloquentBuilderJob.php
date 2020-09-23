<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Illuminate\Database\Eloquent\Builder;

abstract class ChunksEloquentBuilderJob implements ChunksEloquentBuilder
{
    protected int $limit;

    protected int $offset;

    abstract public function getBuilder(): Builder;

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }
}
