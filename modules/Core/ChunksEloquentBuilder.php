<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

interface ChunksEloquentBuilder
{
    public function getLimit(): int;

    public function getOffset(): int;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;
}
