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
    protected $revisions;
    
    /**
     * Create a new job instance.
     *
     * @param array $revisions
     */
    public function __construct(array $revisions)
    {
        $this->revisions = $revisions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \DB::table(Revisionable::newModel()->getTable())->insert($this->revisions);
    }
    
    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        if (is_array($this->revisions) && is_array($this->revisions[0])) {
            $revision = $this->revisions[0];
        } else {
            $revision = $this->revisions;
        }

        return ['Store Revision', $revision['revisionable_type'].':'.$revision['revisionable_id']];
    }
}
