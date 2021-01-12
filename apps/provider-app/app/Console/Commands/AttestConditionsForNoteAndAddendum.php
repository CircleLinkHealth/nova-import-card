<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AttestConditionsForNoteAndAddendum as Job;
use Illuminate\Console\Command;

class AttestConditionsForNoteAndAddendum extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attest conditions to note and addendum';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:attest-problems {problemIds} {noteId} {addendumId?}';

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
        Job::dispatch(
            (string) $this->argument('problemIds'),
            (int) $this->argument('noteId'),
            $this->argument('addendumId') ?? null
        );
    }
}
