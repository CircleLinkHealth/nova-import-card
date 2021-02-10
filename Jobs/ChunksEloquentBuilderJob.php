<?php


namespace CircleLinkHealth\Core\Jobs;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;

abstract class ChunksEloquentBuilderJob implements ChunksEloquentBuilder, ShouldQueue, ShouldBeEncrypted
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