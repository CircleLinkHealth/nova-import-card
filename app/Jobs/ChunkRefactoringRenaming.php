<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChunkRefactoringRenaming implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $table;
    protected string $oldClass;
    protected string $newClass;
    protected Carbon $date;
    protected int $range;
    protected string $field;

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
        $this->table = $table;
        $this->field = $field;
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
        $this->date = $date->startOfMonth()->startOfDay();
        $this->range = $range;
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
