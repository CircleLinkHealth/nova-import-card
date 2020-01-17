<?php

namespace CircleLinkHealth\Revisionable\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CircleLinkHealth\Revisionable\Entities\Revisionable;

class StoreRevisions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array
     */
    protected $revision;
    
    /**
     * Create a new job instance.
     *
     * @param array $revisions
     */
    public function __construct(array $revisions)
    {
        $this->revision = $revisions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \DB::table(Revisionable::newModel()->getTable())->insert($this->revision);
    }
}
