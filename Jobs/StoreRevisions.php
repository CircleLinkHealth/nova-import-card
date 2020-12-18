<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Revisionable\Jobs;

use CircleLinkHealth\Revisionable\Entities\Revisionable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreRevisions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    protected $revisions;

    /**
     * Create a new job instance.
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
