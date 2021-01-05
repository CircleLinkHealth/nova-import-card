<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ChunkRefactoringRenaming;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PostRefactoringRenaming extends Command
{
    const DEFAULT_CHUNK_RANGE = 6;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When we do major refactoring and move classes, we need to update class references in morph tables.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refactor:rename';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        collect($this->classPaths())->each(function ($class) {
            $earliestRow = DB::table($class['table'])
                ->orderBy('created_at')
                ->first();

            if (is_null($earliestRow)) {
                return;
            }

            $this->info("\nChanging {$class['old']} to {$class['new']}.\n");

            $date = Carbon::parse($earliestRow->created_at)->startOfMonth();
            $now = Carbon::now();

            while ($date->lessThanOrEqualTo($now)) {
                ChunkRefactoringRenaming::dispatch(
                    $class['table'],
                    $class['field'],
                    $class['old'],
                    $class['new'],
                    $date,
                    self::DEFAULT_CHUNK_RANGE
                );

                $date->addMonths(self::DEFAULT_CHUNK_RANGE);
            }
        });
    }

    private function classPaths(): array
    {
        return [
            [
                'table' => 'revisions',
                'field' => 'revisionable_type',
                'old'   => 'CircleLinkHealth\Eligibility\Entities\Enrollee',
                'new'   => 'CircleLinkHealth\SharedModels\Entities\Enrollee',
            ],
        ];
    }
}
