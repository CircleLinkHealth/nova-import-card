<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ChunksEloquentBuilderJobV2 implements ChunksEloquentBuilder, ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $limit;

    protected int $offset;

    protected int $total;

    public function dispatchInBatches(int $limit)
    {
        $count  = $this->getCount();
        $offset = 0;

        while ($offset < $count) {
            dispatch(
                $this->setOffset($offset)
                    ->setLimit($limit)
            );
            $offset = $offset + $limit;
        }
    }

    public function getBuilder(): Builder
    {
        return $this->query()
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    public function getCount()
    {
        return $this->unsetWith($this->query())->count();
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

    abstract public function query(): Builder;

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

    public function setTotal(int $total): ChunksEloquentBuilder
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return array Array of Job objects
     */
    public function splitToBatches(int $limit = 1000): array
    {
        $count  = $this->getCount();
        $offset = 0;

        $jobs = [];

        while ($offset < $count) {
            $jobs[] = $this->setOffset($offset)
                ->setLimit($limit);
            $offset = $offset + $limit;
        }

        return $jobs;
    }

    private function unsetWith(Builder $query)
    {
        return $query->without(array_keys($query->getEagerLoads()));
    }
}
