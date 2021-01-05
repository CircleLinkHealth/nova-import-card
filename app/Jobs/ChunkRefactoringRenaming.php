<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChunkRefactoringRenaming implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected Carbon $date;
    protected string $field;
    protected string $newClass;
    protected string $oldClass;
    protected int $range;

    protected string $table;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        string $table,
        string $field,
        string $oldClass,
        string $newClass,
        Carbon $date,
        int $range = 6
    ) {
        $this->table    = $table;
        $this->field    = $field;
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
        $this->date     = $date->startOfMonth()->startOfDay();
        $this->range    = $range;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \DB::table($this->table)
            ->where($this->field, $this->oldClass)
            ->where('created_at', '>=', $this->date)
            ->where('created_at', '<', $this->date->copy()->addMonths($this->range)->endOfMonth()->endOfDay())
            ->update(
                [
                    $this->field => $this->newClass,
                ]
            );
    }
}
