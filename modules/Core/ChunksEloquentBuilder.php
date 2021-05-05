<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

interface ChunksEloquentBuilder
{
    public function getChunkId(): int;

    public function getLimit(): int;

    public function getOffset(): int;

    public function getTotal(): int;

    public function setChunkId(int $chunkId): self;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;

    public function setTotal(int $total): self;
}
