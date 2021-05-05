<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

abstract class ChunksEloquentBuilderJob implements ChunksEloquentBuilder, ShouldQueue, ShouldBeEncrypted
{
    protected int $chunkId;
    protected int $limit;

    protected int $offset;

    protected int $total;

    abstract public function getBuilder(): Builder;

    public function getChunkId(): int
    {
        return $this->chunkId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setChunkId(int $chunkId): self
    {
        $this->chunkId = $chunkId;

        return $this;
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

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }
}
